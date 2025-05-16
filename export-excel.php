<?php

require __DIR__ . "/crest/crest.php";
require __DIR__ . "/crest/crestcurrent.php";
require __DIR__ . "/crest/settings.php";
require __DIR__ . "/utils/index.php";
require __DIR__ . "/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$id = $_GET['id'];

$response = CRest::call('crm.item.list', [
    "entityTypeId" => LISTINGS_ENTITY_TYPE_ID,
    "filter" => ["id" => $id],
    "select" => [
        "ufCrm18ReferenceNumber",
        "ufCrm18OfferingType",
        "ufCrm18PropertyType",
        "ufCrm18SaleType",
        "ufCrm18UnitNo",
        "ufCrm18Size",
        "ufCrm18Bedroom",
        "ufCrm18Bathroom",
        "ufCrm18Parking",
        "ufCrm18LotSize",
        "ufCrm18TotalPlotSize",
        "ufCrm18BuildupArea",
        "ufCrm18LayoutType",
        "ufCrm18TitleEn",
        "ufCrm18DescriptionEn",
        "ufCrm18TitleAr",
        "ufCrm18DescriptionAr",
        "ufCrm18Geopoints",
        "ufCrm18ListingOwner",
        "ufCrm18LandlordName",
        "ufCrm18LandlordEmail",
        "ufCrm18LandlordContact",
        "ufCrm18ReraPermitNumber",
        "ufCrm18ReraPermitIssueDate",
        "ufCrm18ReraPermitExpirationDate",
        "ufCrm18DtcmPermitNumber",
        "ufCrm18Location",
        "ufCrm18BayutLocation",
        "ufCrm18ProjectName",
        "ufCrm18ProjectStatus",
        "ufCrm18Ownership",
        "ufCrm18Developers",
        "ufCrm18BuildYear",
        "ufCrm18Availability",
        "ufCrm18AvailableFrom",
        "ufCrm18RentalPeriod",
        "ufCrm18Furnished",
        "ufCrm18DownPaymentPrice",
        "ufCrm18NoOfCheques",
        "ufCrm18ServiceCharge",
        "ufCrm18PaymentMethod",
        "ufCrm18FinancialStatus",
        "ufCrm18AgentName",
        "ufCrm18ContractExpiryDate",
        "ufCrm18FloorPlan",
        "ufCrm18QrCodePropertyBooster",
        "ufCrm18VideoTourUrl",
        "ufCrm_18_360_VIEW_URL",
        "ufCrm18BrochureDescription",
        "ufCrm_12_BROCHURE_DESCRIPTION_2",
        "ufCrm18PhotoLinks",
        "ufCrm18Notes",
        "ufCrm18PrivateAmenities",
        'ufCrm18CommercialAmenities',
        "ufCrm18Price",
        "ufCrm18Status",
        "ufCrm18HidePrice",
        "ufCrm18PfEnable",
        "ufCrm18BayutEnable",
        "ufCrm18DubizzleEnable",
        "ufCrm18WebsiteEnable",
        "ufCrm18TitleDeed",
        // "ufCrm18City",
        // "ufCrm18Community",
        // "ufCrm18SubCommunity",
        // "ufCrm18Tower",
        // "ufCrm18BayutCity",
        // "ufCrm18BayutCommunity",
        // "ufCrm18BayutSubCommunity",
        // "ufCrm18BayutTower",
        // "ufCrm18AgentId",
        // "ufCrm18AgentEmail",
        // "ufCrm18AgentPhone",
        // "ufCrm18AgentLicense",
        // "ufCrm18AgentPhoto",
        // "ufCrm18Watermark",
    ]
]);

$property = $response['result']['items'][0];

if (!$property) {
    die("Property not found.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

function getExcelColumn($index)
{
    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
}

$columnIndex = 1;
foreach ($property as $key => $value) {
    if (empty($value)) {
        continue;
    }

    $colLetter = getExcelColumn($columnIndex);
    $sheet->setCellValue($colLetter . '1', $key);
    $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
    $sheet->setCellValue($colLetter . '2', is_array($value) ? implode(', ', $value) : $value); // Values
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    $columnIndex++;
}

function sanitizeFileName($filename)
{
    $filename = trim($filename);
    $filename = str_replace(' ', '_', $filename);
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $filename);
    $filename = preg_replace('/_+/', '_', $filename);

    return $filename;
}

$filename = "property_" . sanitizeFileName($property['ufCrm18ReferenceNumber']) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
