<?php

namespace App\Command;

use App\Entity\Event;
use App\Service\Geocoding;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Goutte\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:scrap-ffta-events',
    description: 'Scrap ffta website and fill a database of scraped events',
)]
class ScrapFftaEventsCommand extends Command
{
    private Client $client;
    private Geocoding $geocoding;
    private ManagerRegistry $doctrine;

    const DATE_CONVERSION = [
        'janvier' => '01',
        'fevrier' => '02',
        'mars' => '03',
        'avril' => '04',
        'mai' => '05',
        'juin' => '06',
        'juillet' => '07',
        'août' => '08',
        'septembre' => '09',
        'octobre' => '10',
        'novembre' => '11',
        'décembre' => '12'
    ];

    const DISTANCE_CONVERSION = [
        '' => 0,
        '20m' => 1,
        '30m' => 2,
        '40m' => 4,
        '50m' => 8,
        '60m' => 16,
        '70m' => 32,
    ];

    const COUNTRY_CONVERSION = [
        'USA' => 'United States',
        'CHINE' => 'China',
        'ITALIE' => 'Italy',
        'BULGARIE' => 'Bulgaria',
        'GRANDE BRETAGNE' => 'United Kingdom',
        'ROUMANIE' => 'Romania',
        'COLOMBIE' => 'Colombia',
        'ALGÉRIE' => 'Algeria',
        'ALLEMAGNE' => 'Germany',
        'KOR' => 'Korea',
    ];

    public function __construct(Geocoding $geocoding, ManagerRegistry $doctrine, string $name = null)
    {
        $this->client = new Client(HttpClient::create(['verify_host' => false, 'verify_peer' => false]));
        $this->geocoding = $geocoding;
        $this->doctrine = $doctrine;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('base-url', InputArgument::OPTIONAL, 'base url of ffta website');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $baseUrl = $input->getArgument('base-url') ?? 'https://www.ffta.fr/evenements/liste';
        $entityManager = $this->doctrine->getManager();

        $io->writeln('flushing event table...');
        $this->flushEventTable();
        $io->writeln('done..');

        $io->writeln('fetching events list page...');

        $crawler = $this->client->request('GET', $baseUrl);
        $nodes = $crawler->filter('tbody a[href*="/evenements/"]')->each(function (Crawler $node) {
            return $node->link();
        });

        /** @var Link $node */
        foreach ($nodes as $node) {
            $io->writeln("new event: " . $node->getNode()->textContent);
            $event = new Event();

            // fill name from link content
            $event->setName($node->getNode()->textContent);

            $io->writeln('fetching event details page...');
            $crawler = $this->client->click($node);

            // fill dates from header
            $dateSingle = $crawler->filter('.date-display-single')->text('');
            if ($dateSingle !== '') {
                $event->setDateFrom($this->createDateFromText($dateSingle));
                $event->setDateTo($this->createDateFromText($dateSingle));
            } else {
                $dateFrom = $crawler->filter('.date-display-start');
                $event->setDateFrom($this->createDateFromText($dateFrom->text()));
                $dateTo = $crawler->filter('.date-display-end');
                $event->setDateTo($this->createDateFromText($dateTo->text()));
            }

            // fill data from dl/dt/dd
            $status = $crawler->filterXPath("//dt[contains(string(), 'État')]/following::dd[1]")->text('');
            $event->setStatus($status);

            $organizer = $crawler->filterXPath("//dt[contains(string(), 'Structure Organisatrice')]/following::dd[1]")->text('');
            $event->setOrganizer($organizer);

            $testName = $crawler->filterXPath("//dt[contains(string(), 'Nom de l')]/following::dd[1]")->text('');
            $event->setTestName($testName);

            $testType = $crawler->filterXPath("//dt[contains(string(), 'Type d')]/following::dd[1]")->text('');
            $event->setTestType($testType);

            $regionalCommittee = $crawler->filterXPath("//dt[contains(string(), 'Comité régional')]/following::dd[1]")->text('');
            $event->setRegionalCommittee($regionalCommittee);

            $discipline = $crawler->filterXPath("//dt[contains(string(), 'Discipline')]/following::dd[1]")->text('');
            $event->setDiscipline($discipline);

            $distance = $crawler->filterXPath("//dt[contains(string(), 'Distances proposées')]/following::dd[1]")->text('');

            $event->setDistances($this->formatDistance($distance));

            $description = $crawler->filterXPath("//dt[contains(string(), 'Caractéristique')]/following::dd[1]")->text('');
            $event->setDescription($description);

            $address = $crawler->filterXPath("//dt[contains(string(), 'Adresse')]/following::dd[1]")->text('');
            if (strtolower($address) !== 'france' && $address !== '') {
                $event->setAddress($address);
            }

            // fill address from localisation tab
            if (empty($event->getAddress())) {
                $address = $crawler->filter('.thoroughfare')->text('');
                $address .= ' ' . $crawler->filter('.premise')->text('');
                $address .= ' ' . $crawler->filter('.postal-code')->text('');
                $address = trim($address);

                $locality = $crawler->filter('.locality')->text();
                if (str_contains($locality, ' - ')) {
                    [$city, $country] = explode(' - ', $locality);
                    $country = $this->formatCountry($country);

                    $address .= ' ' . $city;
                    $address .= ', ' . $country;
                } else {
                    $address .= ' ' . $locality;
                }

                $event->setAddress($address);
            }

            $coordinates = $crawler->filterXPath("//dt[contains(string(), 'Itinéraire vers')]/following::dd[1]")->text('');
            if ($coordinates !== '') {
                $coordinates = str_replace(' ', '', $coordinates);
                [$lat, $lon] = explode('/', $coordinates);
                $event->setLatitude((float)$lat);
                $event->setLongitude((float)$lon);
            }

            $locality = $crawler->filter('.locality')->text();
            if (str_contains($locality, ' - ')) {
                [$city, $country] = explode(' - ', $locality);
                $locality = $city . ', ' . $this->formatCountry($country);
            }
            $event->setCity($locality);

            if (empty($event->getLatitude()) || empty($event->getLongitude() === null)) {
                $geolocation = $this->geocoding->getGeolocationFromAddress($event->getAddress());
                if (!empty($geolocation)){
                    $event->setLatitude($geolocation['latitude']);
                    $event->setLongitude($geolocation['longitude']);
                }
            }

            $event->setPhoneNumber($crawler->filter('.field-name-field-telephone')->filterXPath('.//text()')->last()->text(''));
            $event->setMail($crawler->filter('.field-name-field-email')->filterXPath('.//text()')->last()->text(''));
            $event->setWebsite($crawler->filter('.field-name-field-site-organisateur > a')->text(''));

            print_r($event);
            $entityManager->persist($event);
        }

        $io->writeln('Inserting in database...');
        $entityManager->flush();

        $io->success('Database updated!');

        return Command::SUCCESS;
    }

    /**
     * @param string $dateText
     * @return \DateTime
     * @throws \Exception
     */
    public function createDateFromText(string $dateText): \DateTime
    {
        [$day, $month, $year] = explode(' ', $dateText);
        $month = self::DATE_CONVERSION[$month];
        $date = \DateTime::createFromFormat('d m Y H:i:s', "$day $month $year 00:00:00");

        if (!$date instanceof \DateTime) {
            throw new \Exception("can't convert $dateText into a DateTime object");
        }

        return $date;
    }

    public function formatDistance(string $distance): int
    {
        $total = 0;
        $distances = explode(' ', $distance);

        array_map(function ($dis) use (&$total) {
            $total += self::DISTANCE_CONVERSION[$dis];
        }, $distances);

        return $total;
    }

    private function flushEventTable(): void
    {
        $em = $this->doctrine->getManager();

        $cmd = $em->getClassMetadata(Event::class);
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query('DELETE FROM '.$cmd->getTableName());
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
        };
    }

    private function formatCountry(string $country): string
    {
        if (array_key_exists($country, self::COUNTRY_CONVERSION)) {
            return self::COUNTRY_CONVERSION[$country];
        }

        return $country;
    }
}
