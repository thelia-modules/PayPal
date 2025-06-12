<?php

namespace PayPal\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use PayPal\Model\Map\PaypalOrderTableMap;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Thelia\Api\Bridge\Propel\State\PropelCollectionProvider;
use Thelia\Api\Bridge\Propel\State\PropelItemProvider;
use Thelia\Api\Resource\Order;
use Thelia\Api\Resource\PropelResourceInterface;
use Thelia\Api\Resource\PropelResourceTrait;
use Thelia\Api\Resource\ResourceAddonInterface;
use ApiPlatform\Metadata\ApiFilter;
use Thelia\Api\Resource\ResourceAddonTrait;
use Thelia\Api\Bridge\Propel\Filter\SearchFilter;
use Thelia\Api\Bridge\Propel\Filter\OrderFilter;


#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'admin/paypal/orders',
            paginationEnabled: true,
            provider: PropelCollectionProvider::class,
        ),
        new Get(
            uriTemplate: 'admin/paypal/orders/{id}',
            requirements: ['id' => '\d+'],
            provider: PropelItemProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_READ_ADMIN]]
)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'front/paypal/orders',
            paginationEnabled: true,
            provider: PropelCollectionProvider::class,
        ),
        new Get(
            uriTemplate: 'front/paypal/orders/{id}',
            requirements: ['id' => '\d+'],
            provider: PropelItemProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_READ_ADMIN]]
)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: [
        'id' => 'exact',
    ]
)]
#[ApiFilter(
    filterClass: OrderFilter::class,
    properties: [
        'id',
    ]
)]
class PaypalOrder implements PropelResourceInterface, ResourceAddonInterface
{
    public const GROUP_READ_ADMIN = 'admin:paypal_order:read';
    public const GROUP_READ_FRONT = 'front:paypal_order:read';

    use PropelResourceTrait;
    use ResourceAddonTrait;

    #[Groups([Order::GROUP_ADMIN_READ, Order::GROUP_ADMIN_WRITE, Order::GROUP_FRONT_READ, Order::GROUP_FRONT_READ_SINGLE])]
    private ?int $id = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $paymentId = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $agreementId = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $creditCardId = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $state = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $amount = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $description = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $payerId = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $token = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $planifiedTitle = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $planifiedDescription = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $planifiedFrequency = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?int $planifiedFrequencyInterval = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?int $planifiedCycle = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?int $planifiedActualCycle = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $planifiedMinAmount = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?string $planifiedMaxAmount = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups([self::GROUP_READ_ADMIN])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    public function getAgreementId(): ?string
    {
        return $this->agreementId;
    }

    public function setAgreementId(?string $agreementId): self
    {
        $this->agreementId = $agreementId;
        return $this;
    }

    public function getCreditCardId(): ?string
    {
        return $this->creditCardId;
    }

    public function setCreditCardId(?string $creditCardId): self
    {
        $this->creditCardId = $creditCardId;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPayerId(): ?string
    {
        return $this->payerId;
    }

    public function setPayerId(?string $payerId): self
    {
        $this->payerId = $payerId;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getPlanifiedTitle(): ?string
    {
        return $this->planifiedTitle;
    }

    public function setPlanifiedTitle(?string $planifiedTitle): self
    {
        $this->planifiedTitle = $planifiedTitle;
        return $this;
    }

    public function getPlanifiedDescription(): ?string
    {
        return $this->planifiedDescription;
    }

    public function setPlanifiedDescription(?string $planifiedDescription): self
    {
        $this->planifiedDescription = $planifiedDescription;
        return $this;
    }

    public function getPlanifiedFrequency(): ?string
    {
        return $this->planifiedFrequency;
    }

    public function setPlanifiedFrequency(?string $planifiedFrequency): self
    {
        $this->planifiedFrequency = $planifiedFrequency;
        return $this;
    }

    public function getPlanifiedFrequencyInterval(): ?int
    {
        return $this->planifiedFrequencyInterval;
    }

    public function setPlanifiedFrequencyInterval(?int $planifiedFrequencyInterval): self
    {
        $this->planifiedFrequencyInterval = $planifiedFrequencyInterval;
        return $this;
    }

    public function getPlanifiedCycle(): ?int
    {
        return $this->planifiedCycle;
    }

    public function setPlanifiedCycle(?int $planifiedCycle): self
    {
        $this->planifiedCycle = $planifiedCycle;
        return $this;
    }

    public function getPlanifiedActualCycle(): ?int
    {
        return $this->planifiedActualCycle;
    }

    public function setPlanifiedActualCycle(?int $planifiedActualCycle): self
    {
        $this->planifiedActualCycle = $planifiedActualCycle;
        return $this;
    }

    public function getPlanifiedMinAmount(): ?string
    {
        return $this->planifiedMinAmount;
    }

    public function setPlanifiedMinAmount(?string $planifiedMinAmount): self
    {
        $this->planifiedMinAmount = $planifiedMinAmount;
        return $this;
    }

    public function getPlanifiedMaxAmount(): ?string
    {
        return $this->planifiedMaxAmount;
    }

    public function setPlanifiedMaxAmount(?string $planifiedMaxAmount): self
    {
        $this->planifiedMaxAmount = $planifiedMaxAmount;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[Ignore]
    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return PaypalOrderTableMap::getTableMap();
    }

    #[Ignore] public static function getResourceParent(): string
    {
        return Order::class;
    }
}
