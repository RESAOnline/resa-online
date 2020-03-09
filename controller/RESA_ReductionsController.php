<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ReductionsController extends RESA_Controller
{
	public function getSlug()
	{
		return 'resa_reductions';
	}

	public function getPageName()
	{
		return __( 'reductions_title', 'resa' );
	}

	public function isSettings()
	{
		return true;
	}

	public function getClassDir()
	{
		return __DIR__;
	}

	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts()
	{
		return array_merge(self::$GLOBAL_SCRIPTS, array(
			'manager/ReductionsManager',
			'manager/ChangeDetectionManager',
			'manager/FunctionsManager',
			'controller/ReductionsController',
			'directive/timepicker'
		));
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		return array_merge(self::$GLOBAL_STYLES, array(
			'design-back'
		));
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->renderer('RESA_reductions',
			array('days'=>RESA_Variables::getJSONDays(),
					'languages' => json_encode(RESA_Variables::getLanguages()),
					'date_format'=> RESA_Tools::wpToJSDateFormat(),
					'time_format'=> RESA_Tools::wpToJSTimeFormat()));
	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods()
	{
		$this->addAjaxMethod('initializationDataReductions');
		$this->addAjaxMethod('updateReductions');
	}

	/**
	 * Return the initialization data
	 */
	public function initializationDataReductions(){
		$reductions = RESA_Reduction::getAllData();
		$services = RESA_Service::getAllData();
		$settings = array(
			'payment_currency' => get_option('resa_settings_payment_currency'),
			'languages' => unserialize(get_option('resa_settings_languages')),
			'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
			'customer_booking_url' => get_option('resa_settings_customer_booking_url')
		);
		$skeletonReduction = new RESA_Reduction();
		$skeletonReductionConditions = new RESA_ReductionConditions();
		$skeletonReductionCondition = new RESA_ReductionCondition();
		$skeletonReductionConditionService = new RESA_ReductionConditionService();
		$skeletonReductionApplication = new RESA_ReductionApplication();
		$skeletonReductionConditionsApplication = new RESA_ReductionConditionsApplication();
		$skeletonReductionConditionApplication = new RESA_ReductionConditionApplication();

		echo  '{
			"reductions":'.RESA_Tools::formatJSONArray($reductions).',
			"services":'.RESA_Tools::formatJSONArray($services).',
			"settings":'.json_encode($settings).',
			"skeletonReduction":'. $skeletonReduction->toJSON().',
			"skeletonReductionConditions":'. $skeletonReductionConditions->toJSON().',
			"skeletonReductionCondition":'. $skeletonReductionCondition->toJSON().',
			"skeletonReductionConditionService":'. $skeletonReductionConditionService->toJSON().',
			"skeletonReductionApplication":'. $skeletonReductionApplication->toJSON().',
			"skeletonReductionConditionsApplication":'. $skeletonReductionConditionsApplication->toJSON().',
			"skeletonReductionConditionApplication":'. $skeletonReductionConditionApplication->toJSON().',
			"typeCheckReductionConditions":'.json_encode(RESA_Variables::typeCheckReductionConditions()).',
			"typeReductionConditions":'.json_encode(RESA_Variables::typeReductionConditions()).',
			"typeReductionApplications":'.json_encode(RESA_Variables::typeReductionApplications()).',
			"typeApplicationsTypeReduction":'.json_encode(RESA_Variables::typeApplicationsTypeReduction()).',
			"typeApplicationConditionsOn":'.json_encode(RESA_Variables::typeApplicationConditionsOn()).',
			"typeReductionApplicationConditions":'.json_encode(RESA_Variables::typeReductionApplicationConditions()).'
		}';
		wp_die();
	}


	public function updateReductions()
	{
		Logger::INFO('called');
		$data = RESA_Reduction::getAllData();
		if(isset($_REQUEST['reductions'])){
			$reductionsInPost = json_decode(stripslashes(wp_kses_post($_REQUEST['reductions'])));
			$idReductions = array();
			for($i = 0; $i < count($reductionsInPost); $i++) {
				if(isset($reductionsInPost[$i]->isUpdated) && $reductionsInPost[$i]->isUpdated){
					$reduction = new RESA_Reduction();
					$reduction->fromJSON($reductionsInPost[$i]);
					$oldReductionSaved = false;
					$oldId = $reduction->getId();
					if(!$reduction->isNew() &&
						(count(RESA_BookingReduction::getAllData(array('idReduction'=>$reduction->getId()))) > 0 ||
						count(RESA_AppointmentReduction::getAllData(array('idReduction'=>$reduction->getId()))) > 0)){
						$oldReductionSaved = true;
						$oldReduction = new RESA_Reduction();
						$oldReduction->loadById($reduction->getId());
						$oldReduction->setOldReduction(true);
						$oldReduction->save();
						array_push($idReductions, $oldReduction->getId());
						$reduction->setNew();
						$reduction->addLinkOldReduction($oldReduction->getId());
					}
					$reduction->save();
					if(!$reduction->isNew())
						array_push($idReductions, $reduction->getId());
				}
				else array_push($idReductions, $reductionsInPost[$i]->id);
			}

			for($i = 0; $i < count($data); $i++) {
				if(!in_array($data[$i]->getId(), $idReductions)){
					$data[$i]->deleteMe();
				}
			}
			echo RESA_Tools::formatJSONArray(RESA_Reduction::getAllData());
		}
		wp_die();
	}


}
