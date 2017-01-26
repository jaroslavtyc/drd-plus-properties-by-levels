<?php
namespace DrdPlus\PropertiesByLevels;

use DrdPlus\Codes\GenderCode;
use DrdPlus\Codes\Properties\PropertyCode;
use DrdPlus\Person\ProfessionLevels\ProfessionLevels;
use DrdPlus\Properties\Base\Agility;
use DrdPlus\Properties\Base\BaseProperty;
use DrdPlus\Properties\Base\Charisma;
use DrdPlus\Properties\Base\Intelligence;
use DrdPlus\Properties\Base\Knack;
use DrdPlus\Properties\Base\Strength;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Body\Age;
use DrdPlus\Properties\Body\Height;
use DrdPlus\Properties\Body\HeightInCm;
use DrdPlus\Properties\Body\Size;
use DrdPlus\Properties\Body\WeightInKg;
use DrdPlus\PropertiesByFate\PropertiesByFate;
use DrdPlus\Races\Race;
use DrdPlus\Tables\Tables;
use Granam\Strict\Object\StrictObject;

class FirstLevelProperties extends StrictObject
{
    const INITIAL_PROPERTY_INCREASE_LIMIT = 3;

    /** @var PropertiesByFate */
    private $propertiesByFate;
    /** @var Strength */
    private $firstLevelUnlimitedStrength;
    /** @var Strength */
    private $firstLevelStrength;
    /** @var Agility */
    private $firstLevelUnlimitedAgility;
    /** @var Agility */
    private $firstLevelAgility;
    /** @var Knack */
    private $firstLevelUnlimitedKnack;
    /** @var Knack */
    private $firstLevelKnack;
    /** @var Will */
    private $firstLevelUnlimitedWill;
    /** @var Will */
    private $firstLevelWill;
    /** @var Intelligence */
    private $firstLevelUnlimitedIntelligence;
    /** @var Intelligence */
    private $firstLevelIntelligence;
    /** @var Charisma */
    private $firstLevelUnlimitedCharisma;
    /** @var Charisma */
    private $firstLevelCharisma;
    /** @var WeightInKg */
    private $firstLevelWeightInKgAdjustment;
    /** @var WeightInKg */
    private $firstLevelWeightInKg;
    /** @var Size */
    private $firstLevelSize;
    /** @var HeightInCm */
    private $firstLevelHeightInCmAdjustment;
    /** @var HeightInCm */
    private $firstLevelHeightInCm;
    /** @var Height */
    private $firstLevelHeight;
    /** @var Age */
    private $firstLevelAge;

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param PropertiesByFate $propertiesByFate
     * @param ProfessionLevels $professionLevels
     * @param WeightInKg $weightInKgAdjustment
     * @param HeightInCm $heightInCmAdjustment
     * @param Age $age
     * @param Tables $tables
     * @throws Exceptions\TooLowStrengthAdjustment
     */
    public function __construct(
        Race $race,
        GenderCode $genderCode,
        PropertiesByFate $propertiesByFate,
        ProfessionLevels $professionLevels,
        WeightInKg $weightInKgAdjustment,
        HeightInCm $heightInCmAdjustment,
        Age $age,
        Tables $tables
    )
    {
        $this->propertiesByFate = $propertiesByFate;
        $this->setUpBaseProperties($race, $genderCode, $propertiesByFate, $professionLevels, $tables);
        $this->firstLevelWeightInKgAdjustment = $weightInKgAdjustment;
        $this->firstLevelWeightInKg = $this->createFirstLevelWeightInKg(
            $race,
            $genderCode,
            $weightInKgAdjustment,
            $tables
        );
        $this->firstLevelSize = $this->createFirstLevelSize(
            $race,
            $genderCode,
            $tables,
            $propertiesByFate,
            $professionLevels
        );
        $this->firstLevelHeightInCmAdjustment = $heightInCmAdjustment;
        $this->firstLevelHeightInCm = HeightInCm::getIt(
            $race->getHeightInCm($tables) + $heightInCmAdjustment->getValue()
        );
        $this->firstLevelHeight = Height::getIt($this->firstLevelHeightInCm, $tables);
        $this->firstLevelAge = $age;
    }

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param PropertiesByFate $propertiesByFate
     * @param ProfessionLevels $professionLevels
     * @param Tables $tables
     */
    private function setUpBaseProperties(
        Race $race,
        GenderCode $genderCode,
        PropertiesByFate $propertiesByFate,
        ProfessionLevels $professionLevels,
        Tables $tables
    )
    {
        $propertyValues = [];
        foreach (PropertyCode::getBasePropertyPossibleValues() as $basePropertyCode) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $propertyValues[$basePropertyCode] = $this->calculateFirstLevelBaseProperty(
                PropertyCode::getIt($basePropertyCode),
                $race,
                $genderCode,
                $tables,
                $propertiesByFate,
                $professionLevels
            );
        }

        $this->firstLevelUnlimitedStrength = Strength::getIt($propertyValues[PropertyCode::STRENGTH]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelStrength = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedStrength);

        $this->firstLevelUnlimitedAgility = Agility::getIt($propertyValues[PropertyCode::AGILITY]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelAgility = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedAgility);

        $this->firstLevelUnlimitedKnack = Knack::getIt($propertyValues[PropertyCode::KNACK]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelKnack = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedKnack);

        $this->firstLevelUnlimitedWill = Will::getIt($propertyValues[PropertyCode::WILL]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelWill = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedWill);

        $this->firstLevelUnlimitedIntelligence = Intelligence::getIt($propertyValues[PropertyCode::INTELLIGENCE]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelIntelligence = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedIntelligence);

        $this->firstLevelUnlimitedCharisma = Charisma::getIt($propertyValues[PropertyCode::CHARISMA]);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->firstLevelCharisma = $this->getLimitedProperty($race, $genderCode, $tables, $this->firstLevelUnlimitedCharisma);
    }

    /**
     * @param PropertyCode $propertyCode
     * @param Race $race
     * @param GenderCode $genderCode
     * @param Tables $tables
     * @param PropertiesByFate $propertiesByFate
     * @param ProfessionLevels $professionLevels
     * @return int
     * @throws \DrdPlus\Races\Exceptions\UnknownPropertyCode
     */
    private function calculateFirstLevelBaseProperty(
        PropertyCode $propertyCode,
        Race $race,
        GenderCode $genderCode,
        Tables $tables,
        PropertiesByFate $propertiesByFate,
        ProfessionLevels $professionLevels
    )
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return
            $race->getProperty($propertyCode, $genderCode, $tables)
            + $propertiesByFate->getProperty($propertyCode)->getValue()
            + $professionLevels->getFirstLevelPropertyModifier($propertyCode);
    }

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param Tables $tables
     * @param BaseProperty $baseProperty
     * @return BaseProperty
     * @throws \DrdPlus\Races\Exceptions\UnknownPropertyCode
     */
    private function getLimitedProperty(Race $race, GenderCode $genderCode, Tables $tables, BaseProperty $baseProperty)
    {
        $limit = $this->getBasePropertyLimit($race, $genderCode, $tables, $baseProperty);
        if ($baseProperty->getValue() <= $limit) {
            return $baseProperty;
        }

        return $baseProperty::getIt($limit);
    }

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param Tables $tables
     * @param BaseProperty $baseProperty
     * @return int
     * @throws \DrdPlus\Races\Exceptions\UnknownPropertyCode
     */
    private function getBasePropertyLimit(Race $race, GenderCode $genderCode, Tables $tables, BaseProperty $baseProperty)
    {
        return $race->getProperty($baseProperty->getCode(), $genderCode, $tables) + self::INITIAL_PROPERTY_INCREASE_LIMIT;
    }

    /**
     * @return PropertiesByFate
     */
    public function getPropertiesByFate()
    {
        return $this->propertiesByFate;
    }

    /**
     * @return int 0+
     */
    public function getStrengthLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedStrength->getValue() - $this->getFirstLevelStrength()->getValue();
    }

    /**
     * @return int 0+
     */
    public function getAgilityLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedAgility->getValue() - $this->getFirstLevelAgility()->getValue();
    }

    /**
     * @return int 0+
     */
    public function getKnackLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedKnack->getValue() - $this->getFirstLevelKnack()->getValue();
    }

    /**
     * @return int 0+
     */
    public function getWillLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedWill->getValue() - $this->getFirstLevelWill()->getValue();
    }

    /**
     * @return int 0+
     */
    public function getIntelligenceLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedIntelligence->getValue() - $this->getFirstLevelIntelligence()->getValue();
    }

    /**
     * @return int 0+
     */
    public function getCharismaLossBecauseOfLimit()
    {
        return $this->firstLevelUnlimitedCharisma->getValue() - $this->getFirstLevelCharisma()->getValue();
    }

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param WeightInKg $weightInKgAdjustment
     * @param Tables $tables
     * @return WeightInKg
     */
    private function createFirstLevelWeightInKg(
        Race $race,
        GenderCode $genderCode,
        WeightInKg $weightInKgAdjustment,
        Tables $tables
    )
    {
        return WeightInKg::getIt($race->getWeightInKg($genderCode, $tables) + $weightInKgAdjustment->getValue());
    }

    /**
     * @param Race $race
     * @param GenderCode $genderCode
     * @param Tables $tables
     * @param PropertiesByFate $propertiesByFate
     * @param ProfessionLevels $professionLevels
     * @return Size
     * @throws Exceptions\TooLowStrengthAdjustment
     */
    private function createFirstLevelSize(
        Race $race,
        GenderCode $genderCode,
        Tables $tables,
        PropertiesByFate $propertiesByFate,
        ProfessionLevels $professionLevels
    )
    {
        // the race bonus is NOT count for adjustment, doesn't count to size change respectively
        $sizeModifierByStrength = $this->getSizeModifierByStrength(
            $propertiesByFate->getStrength()->getValue()
            + $professionLevels->getFirstLevelStrengthModifier()
        );
        $raceSize = $race->getSize($genderCode, $tables);

        return Size::getIt($raceSize + $sizeModifierByStrength);
    }

    /**
     * @param $firstLevelStrengthAdjustment
     * @return int
     * @throws Exceptions\TooLowStrengthAdjustment
     */
    private function getSizeModifierByStrength($firstLevelStrengthAdjustment)
    {
        if ($firstLevelStrengthAdjustment === 0) {
            return -1;
        }
        if ($firstLevelStrengthAdjustment === 1) {
            return 0;
        }
        if ($firstLevelStrengthAdjustment >= 2) {
            return +1;
        }
        throw new Exceptions\TooLowStrengthAdjustment(
            'First level strength adjustment can not be lesser than zero. Given ' . $firstLevelStrengthAdjustment
        );
    }

    /**
     * @return Strength
     */
    public function getFirstLevelStrength()
    {
        return $this->firstLevelStrength;
    }

    /**
     * @return Agility
     */
    public function getFirstLevelAgility()
    {
        return $this->firstLevelAgility;
    }

    /**
     * @return Knack
     */
    public function getFirstLevelKnack()
    {
        return $this->firstLevelKnack;
    }

    /**
     * @return Will
     */
    public function getFirstLevelWill()
    {
        return $this->firstLevelWill;
    }

    /**
     * @return Intelligence
     */
    public function getFirstLevelIntelligence()
    {
        return $this->firstLevelIntelligence;
    }

    /**
     * @return Charisma
     */
    public function getFirstLevelCharisma()
    {
        return $this->firstLevelCharisma;
    }

    /**
     * @return WeightInKg
     */
    public function getFirstLevelWeightInKgAdjustment()
    {
        return $this->firstLevelWeightInKgAdjustment;
    }

    /**
     * @return WeightInKg
     */
    public function getFirstLevelWeightInKg()
    {
        return $this->firstLevelWeightInKg;
    }

    /**
     * @return Size
     */
    public function getFirstLevelSize()
    {
        return $this->firstLevelSize;
    }

    /**
     * @return HeightInCm
     */
    public function getFirstLevelHeightInCmAdjustment()
    {
        return $this->firstLevelHeightInCmAdjustment;
    }

    /**
     * @return HeightInCm
     */
    public function getFirstLevelHeightInCm()
    {
        return $this->firstLevelHeightInCm;
    }

    /**
     * @return Height
     */
    public function getFirstLevelHeight()
    {
        return $this->firstLevelHeight;
    }

    /**
     * @return Age
     */
    public function getFirstLevelAge()
    {
        return $this->firstLevelAge;
    }

}