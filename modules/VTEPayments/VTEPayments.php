<?php

include_once "modules/Vtiger/CRMEntity.php";
class VTEPayments extends Vtiger_CRMEntity
{
    public $table_name = "vtiger_payments";
    public $table_index = "paymentid";
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = array("vtiger_paymentscf", "paymentid");
    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    public $tab_name = array("vtiger_crmentity", "vtiger_payments", "vtiger_paymentscf");
    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    public $tab_name_index = array("vtiger_crmentity" => "crmid", "vtiger_payments" => "paymentid", "vtiger_paymentscf" => "paymentid");
    /**
     * Mandatory for Listing (Related listview)
     */
    public $list_fields = array("Payment No" => array("payments", "paymentno"), "Amount" => array("payments", "amount_paid"), "Assigned To" => array("crmentity", "smownerid"));
    public $list_fields_name = array("Payment No" => "paymentno", "Amount" => "amount_paid", "Assigned To" => "assigned_user_id");
    public $list_link_field = "amount_paid";
    public $search_fields = array("Payment No" => array("payments", "paymentno"), "Amount" => array("payments", "amount_paid"), "Assigned To" => array("vtiger_crmentity", "assigned_user_id"));
    public $search_fields_name = array("Payment No" => "paymentno", "Amount" => "amount_paid", "Assigned To" => "assigned_user_id");
    public $popup_fields = array("amount_paid");
    public $def_basicsearch_col = "amount_paid";
    public $def_detailview_recname = "amount_paid";
    public $mandatory_fields = array("amount_paid", "assigned_user_id");
    public $default_order_by = "amount_paid";
    public $default_sort_order = "ASC";
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        global $adb;
        if ($eventType == "module.postinstall") {
            self::checkWebServiceEntry();
            self::addWidgetTo();
            self::resetValid();
            self::updateAutoGenerateField("VTEPayments");
            self::updateInvoiceStatus();
            self::updatePaymentStatus();
            self::addButtons();
        } else {
            if ($eventType == "module.disabled") {
                self::removeWidgetTo();
                self::removeButtons();
            } else {
                if ($eventType == "module.enabled") {
                    self::addWidgetTo();
                    self::checkWebServiceEntry();
                    self::updateInvoiceStatus();
                    self::updatePaymentStatus();
                    self::addButtons();
                } else {
                    if ($eventType == "module.preuninstall") {
                        self::removeWidgetTo();
                        self::removeValid();
                        self::removeButtons();
                    } else {
                        if ($eventType == "module.preupdate") {
                        } else {
                            if ($eventType == "module.postupdate") {
                                self::checkWebServiceEntry();
                                self::removeWidgetTo();
                                self::removeButtons();
                                self::addButtons();
                                self::addWidgetTo();
                                self::resetValid();
                                self::updatePaymentStatus();
                                self::updateAutoGenerateField("VTEPayments");
                            }
                        }
                    }
                }
            }
        }
    }
    public static function resetValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("VTEPayments"));
        $adb->pquery("INSERT INTO `vte_modules` (`module`, `valid`) VALUES (?, ?);", array("VTEPayments", "0"));
    }
    public static function removeValid()
    {
        global $adb;
        $adb->pquery("DELETE FROM `vte_modules` WHERE module=?;", array("VTEPayments"));
    }
    public function save_module($module)
    {
        global $adb;
        $q = "SELECT " . $this->def_detailview_recname . " FROM " . $this->table_name . " WHERE " . $this->table_index . " = " . $this->id;
        $result = $adb->pquery($q, array());
        $cnt = $adb->num_rows($result);
        if (0 < $cnt) {
            $label = $adb->query_result($result, 0, $this->def_detailview_recname);
            $q1 = "UPDATE vtiger_crmentity SET label = '" . $label . "' WHERE crmid = " . $this->id;
            $adb->pquery($q1, array());
        }
    }
    public static function updateInvoiceStatus()
    {
        $invoiceModuleModel = Vtiger_Module_Model::getInstance("Invoice");
        $fieldModel = Vtiger_Field_Model::getInstance("invoicestatus", $invoiceModuleModel);
        $picklistValues = $fieldModel->getPicklistValues();
        if (!isset($picklistValues["Partially Paid"])) {
            $fieldModel->setPicklistValues(array("Partially Paid"));
        }
    }
    public static function updatePaymentStatus()
    {
        $paymentModuleModel = Vtiger_Module_Model::getInstance("VTEPayments");
        $fieldModel = Vtiger_Field_Model::getInstance("payment_status", $paymentModuleModel);
        $picklistValues = $fieldModel->getPicklistValues();
        if (!isset($picklistValues["*Failed"])) {
            $fieldModel->setPicklistValues(array("*Failed"));
        }
        if (!isset($picklistValues["Deduction"])) {
            $fieldModel->setPicklistValues(array("Deduction"));
        }
    }
    /**
     * Function to check if entry exsist in webservices if not then enter the entry
     */
    public static function checkWebServiceEntry()
    {
        global $log;
        $log->debug("Entering checkWebServiceEntry() method....");
        global $adb;
        $sql = "SELECT count(id) AS cnt FROM vtiger_ws_entity WHERE name = 'VTEPayments'";
        $result = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($result)) {
            $no = $adb->query_result($result, 0, "cnt");
            if ($no == 0) {
                $adb->pquery("UPDATE vtiger_ws_entity_seq SET id=(SELECT MAX(id) FROM vtiger_ws_entity)", array());
                $tabid = $adb->getUniqueID("vtiger_ws_entity");
                $ws_entitySql = "INSERT INTO vtiger_ws_entity ( id, name, handler_path, handler_class, ismodule ) VALUES" . " (?, 'VTEPayments','include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation' , 1)";
                $res = $adb->pquery($ws_entitySql, array($tabid));
                $log->debug("Entered Record in vtiger WS entity ");
            }
        }
        $log->debug("Exiting checkWebServiceEntry() method....");
    }
    public static function addWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $module = Vtiger_Module::getInstance("VTEPayments");
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
        } else {
            $template_folder = "layouts/v7";
        }
        $link = $template_folder . "/modules/VTEPayments/resources/Payments.js";
        $module->addLink("HEADERSCRIPT", "VTEPayments Header Script", $link);
        $link_css = $template_folder . "/modules/VTEPayments/resources/VTEPayments.css";
        $module->addLink("HEADERCSS", "VTEPayments Header CSS", $link_css);
        global $adb;
        $sql = "SELECT * FROM `vtiger_relatedlists`  WHERE  `label` = 'VTEPaymentsLinkMustHide'";
        $result = $adb->pquery($sql, array());
        if (0 >= $adb->num_rows($result)) {
            $invoice_tab_id = getTabid("Invoice");
            $VTEPayments_tab_id = getTabid("VTEPayments");
            $sql = "INSERT INTO `vtiger_relatedlists` (`tabid`, `related_tabid`, `name`, `sequence`, `label`, `presence`, `actions`)\r\n                        VALUES (?, ?, 'get_dependents_list', '4', 'VTEPaymentsLinkMustHide', '1', '')";
            $result = $adb->pquery($sql, array($invoice_tab_id, $VTEPayments_tab_id));
        }
        $eventhandler_id = $adb->getUniqueID("vtiger_eventhandlers");
        $params_aftersave = array($eventhandler_id, "vtiger.entity.aftersave", "modules/VTEPayments/VTEPaymentsHandler.php", "VTEPaymentsHandler", "", 1, "[]");
        $adb->pquery("INSERT INTO vtiger_eventhandlers(`eventhandler_id`, `event_name`, `handler_path`, `handler_class`, `cond`, `is_active`, `dependent_on`) VALUES (?,?,?,?,?,?,?)", $params_aftersave);
        $eventhandler_id = $adb->getUniqueID("vtiger_eventhandlers");
        $params_afterdelete = array($eventhandler_id, "vtiger.entity.afterdelete", "modules/VTEPayments/VTEPaymentsHandler.php", "VTEPaymentsHandler", "", 1, "[]");
        $adb->pquery("INSERT INTO vtiger_eventhandlers(`eventhandler_id`,`event_name`, `handler_path`, `handler_class`, `cond`, `is_active`, `dependent_on`) VALUES (?,?,?,?,?,?,?)", $params_afterdelete);
        $eventhandler_id = $adb->getUniqueID("vtiger_eventhandlers");
        $params_aftersavefinal = array($eventhandler_id, "vtiger.entity.aftersave.final", "modules/VTEPayments/VTEPaymentsHandler.php", "VTEPaymentsHandler", "", 1, "[]");
        $adb->pquery("INSERT INTO vtiger_eventhandlers(`eventhandler_id`,`event_name`, `handler_path`, `handler_class`, `cond`, `is_active`, `dependent_on`) VALUES (?,?,?,?,?,?,?)", $params_aftersavefinal);
    }
    public static function removeWidgetTo()
    {
        global $adb;
        global $vtiger_current_version;
        $module = Vtiger_Module::getInstance("VTEPayments");
        if (version_compare($vtiger_current_version, "7.0.0", "<")) {
            $template_folder = "layouts/vlayout";
            $vtVersion = "vt6";
            $linkVT6 = $template_folder . "/modules/VTEPayments/resources/Payments.js";
        } else {
            $template_folder = "layouts/v7";
            $vtVersion = "vt7";
        }
        $link = $template_folder . "/modules/VTEPayments/resources/Payments.js";
        if ($module) {
            $module->deleteLink("HEADERSCRIPT", "VTEPayments Header Script", $link);
            if ($vtVersion != "vt6") {
                $module->deleteLink("HEADERSCRIPT", "VTEPayments Header Script", $linkVT6);
            }
        }
        $link_css = $template_folder . "/modules/VTEPayments/resources/VTEPayments.css";
        $module->deleteLink("HEADERCSS", "VTEPayments Header CSS", $link_css);
        global $adb;
        $sql = "SELECT * FROM `vtiger_relatedlists`  WHERE  `label` = 'VTEPaymentsLinkMustHide'";
        $result = $adb->pquery($sql, array());
        if (0 < $adb->num_rows($result)) {
            $invoice_tab_id = getTabid("Invoice");
            $VTEPayments_tab_id = getTabid("VTEPayments");
            $sql = "DELETE FROM `vtiger_relatedlists`\r\n                            WHERE `tabid` = ? AND `related_tabid` =?";
            $result = $adb->pquery($sql, array($invoice_tab_id, $VTEPayments_tab_id));
        }
        $params_handler = array("modules/VTEPayments/VTEPaymentsHandler.php", "VTEPaymentsHandler");
        $adb->pquery("DELETE FROM vtiger_eventhandlers WHERE handler_path = ? AND handler_class = ?", $params_handler);
    }
    public static function updateAutoGenerateField($moduleName)
    {
        global $adb;
        $sql = "DELETE FROM `vtiger_modentity_num` WHERE semodule = '" . $moduleName . "'";
        $adb->pquery($sql);
        $res = $adb->query("SELECT MAX(num_id) num_id FROM `vtiger_modentity_num`;");
        $num_id = $adb->query_result($res, 0, "num_id");
        $num_id++;
        $adb->query("INSERT INTO `vtiger_modentity_num` (`num_id`, `semodule`, `prefix`, `start_id`, `cur_id`, `active`) VALUES ('" . $num_id . "', '" . $moduleName . "', 'PAY', '1', '1', '1')");
        $adb->query("UPDATE `vtiger_modentity_num_seq` SET `id`='" . $num_id . "'");
    }
    public function addButtons()
    {
        include_once "vtlib/Vtiger/Module.php";
        $listModules = array("Invoice");
        foreach ($listModules as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink("DETAILVIEWBASIC", "Payments", "javascript:Payment_Index_Js.showEditView('index.php?module=VTEPayments&view=ManagePayments&invoiceid=\$RECORD\$');");
            }
        }
    }
    public function removeButtons()
    {
        include_once "vtlib/Vtiger/Module.php";
        $listModules = array("Invoice");
        foreach ($listModules as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink("DETAILVIEWBASIC", "Payments", "javascript:Payment_Index_Js.showEditView('index.php?module=VTEPayments&view=ManagePayments&invoiceid=\$RECORD\$');");
            }
        }
    }
}

?>