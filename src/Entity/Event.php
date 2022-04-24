<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ApiResource(
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'event:list']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'event:item']]],
    order: ['id' => 'ASC'],
    paginationEnabled: false
)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['event:list', 'event:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['event:list', 'event:item'])]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $organizer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $test_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $test_type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $regional_committee;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $discipline;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $distances;

    #[ORM\Column(type: 'date')]
    #[Groups(['event:list', 'event:item'])]
    private $date_from;

    #[ORM\Column(type: 'date')]
    #[Groups(['event:list', 'event:item'])]
    private $date_to;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['event:list', 'event:item'])]
    private $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $address;

    #[ORM\Column(type: 'float')]
    #[Groups(['event:list', 'event:item'])]
    private $latitude;

    #[ORM\Column(type: 'float')]
    #[Groups(['event:list', 'event:item'])]
    private $longitude;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $phone_number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $mail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['event:list', 'event:item'])]
    private $website;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created_at;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status !== '' ? $this->status : null;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrganizer(): ?string
    {
        return $this->organizer !== '' ? $this->organizer : null;
    }

    public function setOrganizer(?string $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getTestName(): ?string
    {
        return $this->test_name !== '' ? $this->test_name : null;
    }

    public function setTestName(?string $test_name): self
    {
        $this->test_name = $test_name;

        return $this;
    }

    public function getTestType(): ?string
    {
        return $this->test_type !== '' ? $this->test_type : null;
    }

    public function setTestType(?string $test_type): self
    {
        $this->test_type = $test_type;

        return $this;
    }

    public function getRegionalCommittee(): ?string
    {
        return $this->regional_committee !== '' ? $this->regional_committee : null;
    }

    public function setRegionalCommittee(?string $regional_committee): self
    {
        $this->regional_committee = $regional_committee;

        return $this;
    }

    public function getDiscipline(): ?string
    {
        return $this->discipline !== '' ? $this->discipline : null;
    }

    public function setDiscipline(?string $discipline): self
    {
        $this->discipline = $discipline;

        return $this;
    }

    public function getDistances(): ?int
    {
        return $this->distances;
    }

    #[Groups(['event:list', 'event:item'])]
    public function getDistance(): ?array
    {
        $testDistance = [];

        if ($this->distances & 1) {
            $testDistance[] = 20;
        }
        if ($this->distances & 2) {
            $testDistance[] = 30;
        }
        if ($this->distances & 4) {
            $testDistance[] = 40;
        }
        if ($this->distances & 8) {
            $testDistance[] = 50;
        }
        if ($this->distances & 16) {
            $testDistance[] = 60;
        }
        if ($this->distances & 32) {
            $testDistance[] = 70;
        }

        return $testDistance;
    }

    public function setDistances(?int $distances): self
    {
        $this->distances = $distances;

        return $this;
    }

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->date_from;
    }

    public function setDateFrom(\DateTimeInterface $date_from): self
    {
        $this->date_from = $date_from;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->date_to;
    }

    public function setDateTo(\DateTimeInterface $date_to): self
    {
        $this->date_to = $date_to;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description !== '' ? $this->description : null;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number !== '' ? $this->phone_number : null;
    }

    public function setPhoneNumber(?string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website !== '' ? $this->website : null;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }
}
