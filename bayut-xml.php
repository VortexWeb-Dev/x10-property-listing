<?php
require 'utils/index.php';
require_once __DIR__ . "/crest/settings.php";

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = WEB_HOOK_URL;
$entityTypeId = LISTINGS_ENTITY_TYPE_ID;
$fields = [
    'id',
    'ufCrm18ReferenceNumber',
    'ufCrm18PermitNumber',
    'ufCrm18ReraPermitNumber',
    'ufCrm18DtcmPermitNumber',
    'ufCrm18OfferingType',
    'ufCrm18PropertyType',
    'ufCrm18HidePrice',
    'ufCrm18RentalPeriod',
    'ufCrm18Price',
    'ufCrm18ServiceCharge',
    'ufCrm18NoOfCheques',
    'ufCrm18City',
    'ufCrm18Community',
    'ufCrm18SubCommunity',
    'ufCrm18Tower',
    'ufCrm18BayutCity',
    'ufCrm18BayutCommunity',
    'ufCrm18BayutSubCommunity',
    'ufCrm18BayutTower',
    'ufCrm18TitleEn',
    'ufCrm18TitleAr',
    'ufCrm18DescriptionEn',
    'ufCrm18DescriptionAr',
    'ufCrm18TotalPlotSize',
    'ufCrm18Size',
    'ufCrm18Bedroom',
    'ufCrm18Bathroom',
    'ufCrm18AgentId',
    'ufCrm18AgentName',
    'ufCrm18AgentEmail',
    'ufCrm18AgentPhone',
    'ufCrm18AgentPhoto',
    'ufCrm18BuildYear',
    'ufCrm18Parking',
    'ufCrm18Furnished',
    'ufCrm_18_360_VIEW_URL',
    'ufCrm18PhotoLinks',
    'ufCrm18FloorPlan',
    'ufCrm18Geopoints',
    'ufCrm18AvailableFrom',
    'ufCrm18VideoTourUrl',
    'ufCrm18Developers',
    'ufCrm18ProjectName',
    'ufCrm18ProjectStatus',
    'ufCrm18ListingOwner',
    'ufCrm18Status',
    'ufCrm18PfEnable',
    'ufCrm18BayutEnable',
    'ufCrm18DubizzleEnable',
    'ufCrm18SaleType',
    'ufCrm18WebsiteEnable',
    'updatedTime',
    'ufCrm18TitleDeed',
    'ufCrm18Amenities'
];

$properties = fetchAllProperties($baseUrl, $entityTypeId, $fields, 'bayut');

if (count($properties) > 0) {
    $xml = generateBayutXml($properties);
    echo $xml;
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?><list></list>';
}
