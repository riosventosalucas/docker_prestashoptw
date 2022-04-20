<?php
/**
 * 2020 Packlink
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache License 2.0
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @author    Packlink <support@packlink.com>
 * @copyright 2020 Packlink Shipping S.L
 * @license   http://www.apache.org/licenses/LICENSE-2.0.txt  Apache License 2.0
 */

use Packlink\BusinessLogic\Http\DTO\ParcelInfo;
use Packlink\PrestaShop\Classes\Utility\PacklinkPrestaShopUtility;

/** @noinspection PhpIncludeInspection */
require_once rtrim(_PS_MODULE_DIR_, '/') . '/packlink/vendor/autoload.php';

/**
 * Class DefaultParcelController
 */
class DefaultParcelController extends PacklinkBaseController
{
    /**
     * Retrieves default parcel.
     */
    public function displayAjaxGetDefaultParcel()
    {
        $parcel = $this->getConfigService()->getDefaultParcel();

        if (!$parcel) {
            PacklinkPrestaShopUtility::dieJson();
        }

        PacklinkPrestaShopUtility::dieJson($parcel->toArray());
    }

    /**
     * Saves default parcel.
     */
    public function displayAjaxSubmitDefaultParcel()
    {
        $data = PacklinkPrestaShopUtility::getPacklinkPostData();
        $data['default'] = true;

        try {
            $parcelInfo = ParcelInfo::fromArray($data);
            $this->getConfigService()->setDefaultParcel($parcelInfo);
            PacklinkPrestaShopUtility::dieJson($parcelInfo->toArray());
        } catch (\Packlink\BusinessLogic\DTO\Exceptions\FrontDtoValidationException $e) {
            PacklinkPrestaShopUtility::die400WithValidationErrors($e->getValidationErrors());
        }
    }
}
