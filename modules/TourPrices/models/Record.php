<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class TourPrices_Record_Model extends Vtiger_Record_Model
{
    public function getHotelsNames($relatedModule = 'Hotels')
    {
        global $adb;
        $result = array();
        if (!$this->getId()) {
            return '';
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', 1);
        if(!empty($limit)) {
            $pagingModel->set('limit', 100);
        }
        $relationModel = Vtiger_RelationListView_Model::getInstance($this, $relatedModule);
        $entries = $relationModel->getEntries($pagingModel);
        foreach ($entries as $entry) {
            $result[] = $entry->getName();
        }

        if (empty($entries)) {
            return '';
        } else {
            return implode(',', $result);
        }
    }
    public function getHotelsList()
    {
        global $adb;
        $ids = array();
        if (!$this->getId()) {
            return '';
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', 1);
        if(!empty($limit)) {
            $pagingModel->set('limit', 100);
        }
        $relationModel = Vtiger_RelationListView_Model::getInstance($this, 'Hotels');
        $entries = $relationModel->getEntries($pagingModel);
        foreach ($entries as $entry) {
            $ids[] = (int) $entry->getId();
        }

        if (empty($entries)) {
            return '';
        } else {
            return json_encode($ids);
        }
    }

    /**
     * Function to get list of related airports for cf_2072 field
     * @param Vtiger_Record_Model $recordModel
     * @return string
     */
    public function getAirportsList(Vtiger_Record_Model $recordModel)
    {
        $ids = array();
        if (!$recordModel->getId()) {
            return '';
        }
        if ($this->isEmpty('cf_2072')) {
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', 1);
            if(!empty($limit)) {
                $pagingModel->set('limit', 100);
            }
            $relationModel = Vtiger_RelationListView_Model::getInstance($recordModel, 'Airports', 'Airports');
            $entries = $relationModel->getEntries($pagingModel);
            if (empty($entries)) {
                return '';
            }

            return json_encode(array_keys($entries));
        } else {
            return $this->get('cf_2072');
        }
    }
}

?>