<?php

class VTEPayments_EditPayment_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
//        $this->vteLicense();
    }
    public function vteLicense()
    {
/*        $vTELicense = new VTEPayments_VTELicense_Model("VTEPayments");
        if (!$vTELicense->validate()) {
            header("Location: index.php?module=VTEPayments&view=List&mode=step2");
        }*/
    }
    public function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $payment_id = trim($request->get("payment_id"));
        $sql = "SELECT p.*, pcf.*, CONCAT(c.firstname,' ',c.lastname) as contact_name, a.accountname as account_name,pc.description, pc.smownerid as assigned_user_id\r\n                  FROM vtiger_payments p\r\n                  INNER JOIN  vtiger_crmentity pc ON pc.crmid = p.paymentid\r\n                  INNER JOIN vtiger_paymentscf pcf ON pcf.paymentid=p.paymentid\r\n                  LEFT JOIN  vtiger_account a ON a.accountid = p.organization\r\n                  LEFT JOIN  vtiger_contactdetails c ON c.contactid = p.contact\r\n                WHERE p.paymentid = ?";
        $res = $db->pquery($sql, array($payment_id));
        while ($row = $db->fetch_row($res)) {
            if ($row["date"]) {
                $row["date"] = DateTimeField::convertToUserFormat($row["date"]);
            }
            if ($row["amount_paid"]) {
                $amount_paid = new CurrencyField($row["amount_paid"]);
                $row["amount_paid"] = $amount_paid->getDisplayValue(NULL, true);
            }
            $payments = $row;
        }
        $data = json_encode($payments);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($data);
        $response->emit();
    }
}

?>