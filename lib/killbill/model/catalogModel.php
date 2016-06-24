<?php

/**
 * Model class for catalog.
 */
class Killbill_CatalogModel {

  /**
   * Recurring billing
   */
  const RECURRING_BILLING_MODE_IN_ADVANCE = 'IN_ADVANCE';
  const RECURRING_BILLING_MODE_IN_ARREAR = 'IN_ARREAR';

  /**
   * Currency
   */
  const CURRENCY_USD = 'USD';
  const CURRENCY_EUR = 'EUR';

  /**
   * @var string
   */
  public $schema;

  /**
   * @type DateTime
   */
  public $effectiveDate;

  /**
   * @var string
   */
  public $catalogName;

  /**
   * @var string
   */
  public $recurringBillingMode;

  /**
   * @var Killbill_CatalogModel_Currency[]
   */
  public $currencies = array();

  /**
   * @var Killbill_CatalogModel_Unit[]
   */
  public $units = array();

  /**
   * @var Killbill_CatalogModel_Product[]
   */
  public $products = array();

  /**
   * @var Killbill_CatalogModel_Rule[]
   */
  public $rules = array();

  /**
   * @var Killbill_CatalogModel_Plan[]
   */
  public $plans = array();

  /**
   * @var Killbill_CatalogModel_PriceList[]
   */
  public $priceLists = array();

  /**
   *
   * @return \DOMDocument
   */
  public function toDOM() {
    $dom = new \DOMDocument('1.0', 'UTF-8');

    $catalog = $dom->createElement('catalog');
    $dom->appendChild($catalog);

    $catalog->appendChild($dom->createElement('effectiveDate', date("c", $this->effectiveDate)));
    $catalog->appendChild($dom->createElement('catalogName', $this->catalogName));
    $catalog->appendChild($dom->createElement('recurringBillingMode', $this->recurringBillingMode));

    $currencies = $dom->createElement('currencies');
    $catalog->appendChild($currencies);
    foreach ($this->currencies as $currencyModel) {
      $currencies->appendChild($dom->createElement('currency', $currencyModel->currency));
    }

    if (!empty($this->units)) {
      $units = $dom->createElement('units');
      $catalog->appendChild($units);
      foreach ($this->units as $unit) {
        $unitsUnit = $dom->createElement('unit');
        $units->appendChild($unitsUnit);
        $unitsUnit->setAttribute('name', $unit->getId());
      }
    }

    $products = $dom->createElement('products');
    $catalog->appendChild($products);
    foreach ($this->products as $productModel) {
      $product = $dom->createElement('product');
      $product->setAttribute('name', $productModel->getId());
      $products->appendChild($product);

      $productCategory = $dom->createElement('category', $productModel->productCategory);
      $product->appendChild($productCategory);
    }

    $rules = $dom->createElement('rules');
    $catalog->appendChild($rules);
    foreach ($this->rules as $ruleModel) {

    }

    $plans = $dom->createElement('plans');
    $catalog->appendChild($plans);
    foreach ($this->plans as $planModel) {
      $plan = $dom->createElement('plan');
      $plan->setAttribute('name', $planModel->getId());
      $plans->appendChild($plan);

      $plan->appendChild($dom->createElement('product', $planModel->product->getId()));

      $planFinalPhase = $dom->createElement('finalPhase');
      $planFinalPhase->setAttribute('type', $planModel->finalPhase->type);
      $plan->appendChild($planFinalPhase);

      $planFinalPhaseDuration = $dom->createElement('duration');
      $planFinalPhase->appendChild($planFinalPhaseDuration);

      $planFinalPhaseDurationUnit = $dom->createElement('unit', $planModel->finalPhase->duration->unit);
      $planFinalPhaseDuration->appendChild($planFinalPhaseDurationUnit);

      if ($planModel->finalPhase->duration->number != null) {
        $planFinalPhaseDurationNumber = $dom->createElement('number', $planModel->finalPhase->duration->number);
        $planFinalPhaseDuration->appendChild($planFinalPhaseDurationNumber);
      }

      if ($planModel->finalPhase->recurring !== null) {
        $planFinalPhaseRecurring = $dom->createElement('recurring');
        $planFinalPhase->appendChild($planFinalPhaseRecurring);

        $planFinalPhaseRecurring->appendChild($dom->createElement('billingPeriod', $planModel->finalPhase->recurring->billingPeriod));

        $planFinalPhaseRecurringPrice = $dom->createElement('recurringPrice');
        $planFinalPhaseRecurring->appendChild($planFinalPhaseRecurringPrice);

        foreach ($planModel->finalPhase->recurring->recurringPrice as $recurringPrice) {
          $planFinalPhaseRecurringPricePrice = $dom->createElement('price');
          $planFinalPhaseRecurringPrice->appendChild($planFinalPhaseRecurringPricePrice);

          $planFinalPhaseRecurringPricePrice->appendChild($dom->createElement('currency', $recurringPrice->currency));
          $planFinalPhaseRecurringPricePrice->appendChild($dom->createElement('value', $recurringPrice->value));
        }
      }

      if ($planModel->finalPhase->usages !== null) {
        $planFinalPhaseUsages = $dom->createElement('usages');
        $planFinalPhase->appendChild($planFinalPhaseUsages);

        foreach ($planModel->finalPhase->usages as $usage) {
          $planFinalPhaseUsagesUsage = $dom->createElement('usage');
          $planFinalPhaseUsages->appendChild($planFinalPhaseUsagesUsage);
          $planFinalPhaseUsagesUsage->setAttribute('name', $usage->getId());
          $planFinalPhaseUsagesUsage->setAttribute('billingMode', $usage->billingMode);
          $planFinalPhaseUsagesUsage->setAttribute('usageType', $usage->usageType);

          if (!empty($usage->billingPeriod)) {
            $planFinalPhaseUsagesUsage->appendChild($dom->createElement('billingPeriod', $usage->billingPeriod));
          }

          if (!empty($usage->limits)) {
            $planFinalPhaseUsagesUsageLimits = $dom->createElement('limits');
            $planFinalPhaseUsagesUsage->appendChild($planFinalPhaseUsagesUsageLimits);

            foreach ($usage->limits as $limit) {
              $planFinalPhaseUsagesUsageLimitsLimit = $dom->createElement('limit');
              $planFinalPhaseUsagesUsageLimits->appendChild($planFinalPhaseUsagesUsageLimitsLimit);

              $planFinalPhaseUsagesUsageLimitsLimit->appendChild($dom->createElement('unit', $limit->unit->getId()));
              if (!empty($limit->min)) {
                $planFinalPhaseUsagesUsageLimitsLimit->appendChild($dom->createElement('min', $limit->min));
              }
              if (!empty($limit->max)) {
                $planFinalPhaseUsagesUsageLimitsLimit->appendChild($dom->createElement('max', $limit->max));
              }
            }
          }

          if (!empty($usage->blocks)) {
            $planFinalPhaseUsagesUsageBlocks = $dom->createElement('blocks');
            $planFinalPhaseUsagesUsage->appendChild($planFinalPhaseUsagesUsageBlocks);

            foreach ($usage->blocks as $block) {
              $planFinalPhaseUsagesUsageBlocksBlock = $dom->createElement('block');
              $planFinalPhaseUsagesUsageBlocks->appendChild($planFinalPhaseUsagesUsageBlocksBlock);

              $planFinalPhaseUsagesUsageBlocksBlock->appendChild($dom->createElement('unit', $block->unit->getId()));
              $planFinalPhaseUsagesUsageBlocksBlock->appendChild($dom->createElement('size', $block->size));

              $planFinalPhaseUsagesUsageBlocksBlockPrices = $dom->createElement('prices');
              $planFinalPhaseUsagesUsageBlocksBlock->appendChild($planFinalPhaseUsagesUsageBlocksBlockPrices);

              foreach ($block->prices as $price) {
                $planFinalPhaseUsagesUsageBlocksBlockPricesPrice = $dom->createElement('price');
                $planFinalPhaseUsagesUsageBlocksBlockPrices->appendChild($planFinalPhaseUsagesUsageBlocksBlockPricesPrice);

                $planFinalPhaseUsagesUsageBlocksBlockPricesPrice->appendChild($dom->createElement('currency', $price->currency));
                $planFinalPhaseUsagesUsageBlocksBlockPricesPrice->appendChild($dom->createElement('value', $price->value));
              }
            }
          }

          if (!empty($usage->recurringPrice)) {
            $planFinalPhaseUsagesUsageRecurringPrice = $dom->createElement('recurringPrice');
            $planFinalPhaseUsagesUsage->appendChild($planFinalPhaseUsagesUsageRecurringPrice);

            foreach ($usage->recurringPrice as $recurringPrice) {
              $planFinalPhaseUsagesUsageRecurringPricePrice = $dom->createElement('price');
              $planFinalPhaseUsagesUsageRecurringPrice->appendChild($planFinalPhaseUsagesUsageRecurringPricePrice);

              $planFinalPhaseUsagesUsageRecurringPricePrice->appendChild($dom->createElement('currency', $recurringPrice->currency));
              $planFinalPhaseUsagesUsageRecurringPricePrice->appendChild($dom->createElement('value', $recurringPrice->value));
            }
          }
        }
      }
    }

    $priceLists = $dom->createElement('priceLists');
    $catalog->appendChild($priceLists);
    foreach ($this->priceLists as $priceListModel) {
      $priceList = $dom->createElement($priceListModel->type);
      $priceList->setAttribute('name', $priceListModel->getId());
      $priceLists->appendChild($priceList);

      $priceListPlans = $dom->createElement('plans');
      $priceList->appendChild($priceListPlans);

      foreach ($priceListModel->plans as $planModel) {
        $priceListPlans->appendChild($dom->createElement('plan', $planModel->getId()));
      }
    }

    return $dom;
  }

  /**
   * Validate
   * @return array
   */
  public function validate() {
    $dom = $this->toDOM();

    $output = [];
    libxml_use_internal_errors(true);
    if (!$dom->schemaValidate($this->schema)) {
      $output = libxml_get_errors();
      libxml_clear_errors();
    }
    libxml_use_internal_errors(false);

    return $output;
  }

}

/**
 * Model class currency
 */
class Killbill_CatalogModel_Currency {

  /**
   * @var string
   */
  public $currency;

  /**
   * Constructor.
   * @param string $currency
   */
  public function __construct($currency) {
    $this->currency = $currency;
  }

}

/**
 * Model class unit
 */
class Killbill_CatalogModel_Unit {

  /**
   * @var string 
   */
  public $name;

  /**
   * Constructor.
   * @param string $name
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Get ID.
   * @return string
   */
  public function getId() {
    return Killbill_CatalogModel_Helpers::strToId($this->name);
  }

}

/**
 * Model class product
 */
class Killbill_CatalogModel_Product {

  const PRODUCT_CATEGORY_BASE = 'BASE';
  const PRODUCT_CATEGORY_ADD_ON = 'ADD_ON';
  const PRODUCT_CATEGORY_STANDALONE = 'STANDALONE';

  /**
   * @var string
   */
  public $productCategory;

  /**
   * @var string
   */
  public $name;

  /**
   * Constructor.
   * @param string $name
   * @param string $productCategory
   */
  public function __construct($name, $productCategory = null) {
    $this->name = $name;
    $this->productCategory = $productCategory == null ? self::PRODUCT_CATEGORY_BASE : $productCategory;
  }

  /**
   * Get ID.
   * @return string
   */
  public function getId() {
    return Killbill_CatalogModel_Helpers::strToId($this->name);
  }

}

/**
 * Model class rule
 */
class Killbill_CatalogModel_Rule {

}

/**
 * Model class plan
 */
class Killbill_CatalogModel_Plan {

  /**
   * @var string
   */
  public $name;

  /**
   * @var Killbill_CatalogModel_Product
   */
  public $product;

  /**
   * @var Killbill_CatalogModel_PlanPhase
   */
  public $finalPhase;

  /**
   * Constructor
   */
  public function __construct($name, Killbill_CatalogModel_Product $product, Killbill_CatalogModel_PlanPhase $finalPhase) {
    $this->name = $name;
    $this->product = $product;
    $this->finalPhase = $finalPhase;
  }

  /**
   * Get ID.
   * @return string
   */
  public function getId() {
    return Killbill_CatalogModel_Helpers::strToId($this->name);
  }

}

/**
 * Model class planPhase
 */
class Killbill_CatalogModel_PlanPhase {

  const TYPE_TRIAL = 'TRIAL';
  const TYPE_DISCOUNT = 'DISCOUNT';
  const TYPE_FIXEDTERM = 'FIXEDTERM';
  const TYPE_EVERGREEN = 'EVERGREEN';

  /**
   * @var string
   */
  public $type;

  /**
   * @var Killbill_CatalogModel_PlanPhase_Duration
   */
  public $duration;

  /**
   * @var Killbill_CatalogModel_PlanPhase_Recurring
   */
  public $recurring = null;

  /**
   * @var Killbill_CatalogModel_PlanPhase_Usage[]
   */
  public $usages = null;

  /**
   * Constructor
   */
  public function __construct($type, Killbill_CatalogModel_PlanPhase_Duration $duration, Killbill_CatalogModel_PlanPhase_Recurring $recurring = null, array $usages = null) {
    $this->type = $type;
    $this->duration = $duration;
    $this->recurring = $recurring;
    $this->usages = $usages;
  }

}

/**
 * Model class planPhase duration
 */
class Killbill_CatalogModel_PlanPhase_Duration {

  const UNIT_DAYS = 'DAYS';
  const UNIT_MONTHS = 'MONTHS';
  const UNIT_YEARS = 'YEARS';
  const UNIT_UNLIMITED = 'UNLIMITED';

  /**
   * @var string
   */
  public $unit;

  /**
   * @var integer
   */
  public $number = null;

  /**
   * Constructor
   */
  public function __construct($unit, $number = null) {
    $this->unit = $unit;
    $this->number = $number;
  }

}

/**
 * Model class planPhase recurring
 */
class Killbill_CatalogModel_PlanPhase_Recurring {

  const BILLING_PERIOD_MONTHLY = 'MONTHLY';
  const BILLING_PERIOD_QUARTERLY = 'QUARTERLY';
  const BILLING_PERIOD_BIANNUAL = 'BIANNUAL';
  const BILLING_PERIOD_ANNUAL = 'ANNUAL';
  const BILLING_PERIOD_BIENNIAL = 'BIENNIAL';
  const BILLING_PERIOD_NO_BILLING_PERIOD = 'NO_BILLING_PERIOD';

  /**
   * @var string
   */
  public $billingPeriod;

  /**
   * @var Killbill_CatalogModel_Price[]
   */
  public $recurringPrice;

  /**
   * Constructor
   */
  public function __construct($billingPeriod, array $recurringPrice) {
    $this->billingPeriod = $billingPeriod;
    $this->recurringPrice = $recurringPrice;
  }

}

/**
 * Model class planPhase usage
 */
class Killbill_CatalogModel_PlanPhase_Usage {

  /**
   * Usage type
   */
  const USAGE_TYPE_CAPACITY = 'CAPACITY';
  const USAGE_TYPE_CONSUMABLE = 'CONSUMABLE';

  /**
   * @var string 
   */
  public $name;

  /**
   * @var string 
   */
  public $billingMode;

  /**
   * @var string
   */
  public $usageType;

  /**
   * @var string
   */
  public $billingPeriod = null;

  /**
   * @var Killbill_CatalogModel_Limit[]
   */
  public $limits = null;

  /**
   * @var Killbill_CatalogModel_Block[]
   */
  public $blocks = null;

  /**
   * @var Killbill_CatalogModel_Price[]
   */
  public $recurringPrice = null;

  /**
   * Constructor
   */
  public function __construct($name, $billingMode, $usageType, $billingPeriod = null, array $limits = null, array $blocks = null, array $recurringPrice = null) {
    $this->name = $name;
    $this->billingMode = $billingMode;
    $this->usageType = $usageType;
    $this->billingPeriod = $billingPeriod;
    $this->limits = $limits;
    $this->blocks = $blocks;
    $this->recurringPrice = $recurringPrice;
  }

  /**
   * Get ID.
   * @return string
   */
  public function getId() {
    return Killbill_CatalogModel_Helpers::strToId($this->name);
  }

}

/**
 * Model class block
 */
class Killbill_CatalogModel_Block {

  /**
   * Block type
   */
  const BLOCK_TYPE_VANILLA = 'VANILLA';
  const BLOCK_TYPE_TOP_UP = 'TOP_UP';
  const BLOCK_TYPE_TIERED = 'TIERED';

  /**
   * @var string
   */
  public $type;

  /**
   * @var \Killbill_CatalogModel_Unit
   */
  public $unit;

  /**
   * @var integer
   */
  public $size;

  /**
   * @var \Killbill_CatalogModel_Price[]
   */
  public $prices;

  /**
   * Constructor
   */
  public function __construct($type, Killbill_CatalogModel_Unit $unit, $size, array $prices) {
    $this->type = $type;
    $this->unit = $unit;
    $this->size = $size;
    $this->prices = $prices;
  }

}

/**
 * Model class limit
 */
class Killbill_CatalogModel_Limit {

  /**
   * @var Killbill_CatalogModel_Unit
   */
  public $unit;

  /**
   * @var integer
   */
  public $min = null;

  /**
   * @var integer
   */
  public $max = null;

  /**
   * Constructor
   */
  public function __construct(Killbill_CatalogModel_Unit $unit, $min = null, $max = null) {
    $this->unit = $unit;
    $this->min = $min;
    $this->max = $max;
  }

}

/**
 * Model class price
 */
class Killbill_CatalogModel_Price {

  /**
   * @var string
   */
  public $currency;

  /**
   * @var float
   */
  public $value;

  /**
   * Constructor
   */
  public function __construct($currency, $value) {
    $this->currency = $currency;
    $this->value = $value;
  }

}

/**
 * Model class price list
 */
class Killbill_CatalogModel_PriceList {

  const TYPE_DEFAULT = 'defaultPriceList';
  const TYPE_CHILD = 'childPriceList';

  /**
   * @var string 
   */
  public $type;

  /**
   * @var string
   */
  public $name;

  /**
   * @var Killbill_CatalogModel_Plan[]
   */
  public $plans = array();

  /**
   * Constructor
   */
  public function __construct($name, array $plans, $type = null) {
    $this->name = $name;
    $this->plans = $plans;
    $this->type = $type == null ? self::TYPE_DEFAULT : $type;
  }

  /**
   * Get ID.
   * @return string
   */
  public function getId() {
    return Killbill_CatalogModel_Helpers::strToId($this->name);
  }

}

/**
 * Helpers.
 */
class Killbill_CatalogModel_Helpers {

  public static function strToId($str) {
    return strtolower(str_replace(' ', '_', preg_replace("/[^A-Za-z0-9 ]/", '', $str)));
  }

}
