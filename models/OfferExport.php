<?php namespace Lovata\Shopaholic\Models;

use Lovata\Toolbox\Classes\Helper\AbstractExportModelInCSV;

/**
 * Class OfferExport
 *
 * @package Lovata\Shopaholic\Models
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 */
class OfferExport extends AbstractExportModelInCSV
{
    const RELATION_MEASURE         = 'measure';
    const RELATION_MEASURE_OF_UNIT = 'measure_of_unit';

    const RELATION_LIST = [
        self::RELATION_MEASURE,
        self::RELATION_MEASURE_OF_UNIT,
    ];

    /** @var string */
    public $table = 'lovata_shopaholic_offers';

    /**
     * Get item list.
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Offer[]
     */
    protected function getItemList()
    {
        return Offer::with($this->arRelationColumnList)->get();
    }

    /**
     * Prepare model relations data.
     * @param Offer $obOffer
     * @return array
     */
    protected function prepareModelRelationsData($obOffer) : array
    {
        $arResult = [];

        if (empty($this->arRelationColumnList)) {
            return $arResult;
        }

        if (!empty($obOffer->measure) && in_array(self::RELATION_MEASURE, $this->arRelationColumnList)) {
            $arResult[self::RELATION_MEASURE] = $obOffer->measure->name;
        }
        if (!empty($obOffer->measure_of_unit)
            && in_array(self::RELATION_MEASURE_OF_UNIT, $this->arRelationColumnList)) {
            $arResult[self::RELATION_MEASURE_OF_UNIT] = $obOffer->measure_of_unit->name;
        }

        return $arResult;
    }
}
