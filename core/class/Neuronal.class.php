<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class Neuronal extends eqLogic {
	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'Neuronal_update';
		$return['progress_file'] = '/tmp/compilation_Neuronal_in_progress';
		$return['state'] = 'nok';
		if (exec('grep -q "extension=fann.so" /etc/php5/apache2/php.ini') ==1)
			$return['state'] = 'ok';
		if (exec('dpkg -s libfann-dev | grep -c "Status: install"') ==1)
			$return['state'] = 'ok';
		return $return;
	}
	public static function dependancy_install() {
		if (file_exists('/tmp/compilation_Neuronal_in_progress')) {
			return;
		}
		log::remove('Neuronal_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('Neuronal_update') . ' 2>&1 &';
		exec($cmd);
	}
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'Neuronal';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		foreach(eqLogic::byType('Neuronal') as $Neuronal){
			$listener = listener::byClassAndFunction('Neuronal', 'ListenerEvent', array('eqLogic_id' => intval($Neuronal->getId())));
			if (!is_object($listener))
				return $return;
		}
		$return['state'] = 'ok';
		return $return;
	}
	public static function deamon_start($_debug = false) {
		log::remove('Neuronal');
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		if ($deamon_info['state'] == 'ok') 
			return;
		foreach(eqLogic::byType('Neuronal') as $Neuronal)
			$Neuronal->save();
	}
	public static function deamon_stop() {
		foreach(eqLogic::byType('Neuronal') as $Neuronal){
			$listener = listener::byClassAndFunction('Neuronal', 'ListenerEvent', array('eqLogic_id' => intval($Neuronal->getId())));
			if (is_object($listener))
				$listener->remove();
		}
	}
	public static function ListenerEvent($_options) {
		log::add('Neuronal', 'debug', 'Objet mis à jour => ' . json_encode($_options));
		$eqLogic=eqLogic::byId($_options['eqLogic_id']);
		if (is_object($eqLogic)) {
			foreach($eqLogic->getConfiguration('sotries') as $Cmd){
				$cmd=cmd::byId(str_replace('#','',$Cmd['cmd']));
				if(is_object($cmd)){
					if($_options['event_id'] == str_replace('#','',$cmd->getValue())){
						log::add('Neuronal','debug','Evenement sur une sortie de Neurone');
						$eqLogic->CreateApprentissageTable();
					}
				}
			}
			foreach($eqLogic->getConfiguration('entrees') as $Cmd){
				if($_options['event_id'] == str_replace('#', '', $Cmd['cmd'])){
	      				log::add('Neuronal','debug','Evenement sur une entree de Neurone');
					$eqLogic->ExecNeurone();
				}
			}
		}
	}
	public function AddCommande($Name,$_logicalId,$type='info',$sousType='other',$template='') {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande)){
			$Commande = new NeuronalCmd();
			$Commande->setId(null);
			$Commande->setName($Name);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
		}
		$Commande->setType($type);
		$Commande->setSubType($sousType);
     		$Commande->setTemplate('dashboard',$template);
		$Commande->setTemplate('mobile', $template);
		$Commande->save();
	}	
	public function postSave() {
	      		$this->createListener();
			$this->AddCommande('Validité du calibrage','calibValid','info','binary','neurCalibValid');
		}
	public function preRemove() {
		$listener = listener::byClassAndFunction('Neuronal', 'ListenerEvent', array('eqLogic_id' => intval($this->getId())));
		if (is_object($listener)) 
			$listener->remove();
	}
	public function createListener(){
		$listener = listener::byClassAndFunction('Neuronal', 'ListenerEvent', array('eqLogic_id' => intval($this->getId())));
		if (!is_object($listener)) {
			log::add('Neuronal','debug','Creation d\'un écouteur d\'evenement :'.$this->getHumanName());
			$listener = new listener();
			$listener->setClass('Neuronal');
			$listener->setFunction('ListenerEvent');
			$listener->setOption(array('eqLogic_id' => intval($this->getId())));
		}
		$listener->emptyEvent();
		foreach ($this->getConfiguration('entrees') as $cmdNeurone) {
			$cmd=cmd::byId(str_replace('#','',$cmdNeurone['cmd']));
			if(is_object($cmd)){
				$listener->addEvent($cmd->getId());
				log::add('Neuronal','debug','Ajout de '.$cmd->getHumanName().' de l\'écouteur d\'evenement :'.$this->getHumanName());
			}
		}
		foreach ($this->getConfiguration('sotries') as $cmdNeurone) {
			$cmd=cmd::byId(str_replace('#','',$cmdNeurone['cmd']));
			if(is_object($cmd)){
				$listener->addEvent($cmd->getValue());
				log::add('Neuronal','debug','Ajout de '.$cmd->getHumanName().' de l\'écouteur d\'evenement :'.$this->getHumanName());
			}
		}
		$listener->save();
		log::add('Neuronal','debug','Lancement de l\'écouteur d\'evenement :'.$this->getHumanName());
	}
	public function ExecNeurone() {	
		$NbCalibration=count($this->getConfiguration('calibration'));
		$NbEntree=count($this->getConfiguration('entrees'));
		$NbSorite=count($this->getConfiguration('sotries'));
		$num_layers = 3;
		$num_neurons_hidden = 70;
		$max_epochs = 5000000;
		$epochs_between_reports = 1000;
		$desired_error = 0.001;
		//$ann = fann_create_standard($num_layers, $NbEntree, $num_neurons_hidden, $NbSorite);
		$ann=fann_create_train($NbCalibration,$NbEntree,$NbSorite);
		if ($ann) {
			$data=array();
			foreach ($this->getConfiguration('calibration') as $calibration) {
				$ligne=array();
				foreach ($this->getConfiguration('calibration') as $calibration) {
					$ligne[]=$calibration['cmd'];
				}
				$data[]=$ligne;
			}
			if(fann_train_on_data($ann,$data,$max_epochs,$epochs_between_reports,$desired_error)){
				$input=array();
				foreach ($this->getConfiguration('entrees') as $cmdNeurone) {
					$cmd = cmd::byId(str_replace('#', '', $cmdNeurone['cmd']));
					if(is_object($cmd)){
						log::add('Neuronal','debug','Ajout d\'une valeur a la table de calibration pour le neurone :'.$this->getHumanName().$cmd->getHumanName());
						$input[]=$cmd->execCmd();
					}
				}
				$Valeurs = fann_run($ann, $input);
				log::add('Neuronal','debug',$this->getHumanName().' Resultat: '.json_encode($Valeurs));
				//if($this->getCmd(null,'calibValid')->execCmd())
					//$this->UpdateOutNeurone($Valeurs)
			}
			fann_destroy($ann);
		}
	}
	public function UpdateOutNeurone($Valeurs) {
		$sortie=0;
		foreach ($this->getConfiguration('sotries') as $cmdNeurone) {
			$cmd = cmd::byId(str_replace('#', '', $cmdNeurone['cmd']));
			if(is_object($cmd)){
				switch ($cmd->getSubType()) {
					case 'slider':    
						$_options['slider']=$Valeurs[$sortie];
					break;
					case 'color':
						$_options['color']=$Valeurs[$sortie];
					break;
					case 'message':
						$_options['titre']=$Valeurs[$sortie];
						$_options['message']=$Valeurs[$sortie];
					break;
					case 'other':
						$_options=null;
					break;
				}
				log::add('Neuronal','debug',$this->getHumanName().': Execution de la sortie '.$cmd->getHumanName().' '. json_encode($_options));
				$cmd->execute($_options);
			}
			$sortie++;
		}
	}
	public function checkdate($date) {
		return $date;
	}
	public function CreateApprentissageTable() {
		$newCalibration=array();
		foreach ($this->getConfiguration('entrees') as $cmdNeurone) {
			$cmd = cmd::byId(str_replace('#', '', $cmdNeurone['cmd']));
			if(is_object($cmd))
				$newCalibration['#'.$cmd->getHumanName().'#']=$cmd->execCmd();
		}
		foreach ($this->getConfiguration('sotries') as $cmdNeurone) {
			$cmd = cmd::byId(str_replace('#', '', $cmdNeurone['cmd']));
			if(is_object($cmd))
				$newCalibration['#'.$cmd->getHumanName().'#']=$cmd->getCmdValue()->execCmd();
		}
		log::add('Neuronal','debug',$this->getHumanName().': Nouvelle calibration: '.json_encode($newCalibration));
		$Calibrations=$this->getConfiguration('calibration');
		foreach ($Calibrations as $Calibration) {
			if($this->getConfiguration('temporel'))
				$newCalibration['datetime']=$this->checkdate($Calibration['datetime']);
			if(count(array_diff($Calibration,$newCalibration)) == 0)
				return;
		}
		$Calibrations[]=$newCalibration;
		$this->setConfiguration('calibration',$Calibrations);
		$this->save();
		log::add('Neuronal','debug','Mise a jours de la table de calibration pour le neurone :'.$this->getHumanName());
	}
}
class NeuronalCmd extends cmd {
	public function execute($_options = array())	{
	}
}
?>
