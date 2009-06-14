<?php
class dbntvController extends Controller {
	public function importGuide() {
		$doc = new SimpleXMLElement(file_get_contents(ROOT_PATH . 'files/xmltv-g.xml'));
		
		cms::$db->sql_query('TRUNCATE TABLE viennacms_dbntv_channels'); // TODO: make this use models
		cms::$db->sql_query('TRUNCATE TABLE viennacms_dbntv_programs');
		
		// TODO: controller loading should vinclude
		
		$chI = 0;
		foreach ($doc->channel as $ch) {
			$chname = (string)$ch->{'display-name'}[0];
			
			if (isset($ch->{'display-name'}[3])) {
				$chname = (string)$ch->{'display-name'}[3];
			}
			
			$channel = new Channel();
			$channel->channel_id = (string)$ch['id'];
			$channel->channel_name = $chname;
			$channel->channel_country = 'nl';
			$channel->write();
			
			$chI++;
		}
		
		$pI = 0;
		
		foreach ($doc->programme as $pr) {
			$program = new Program();
			$program->program_start = strtotime((string)$pr['start']);
			$program->program_end = strtotime((string)$pr['stop']);
			$program->program_title = (string)$pr->title;
			$program->program_description = (string)$pr->desc;
			$program->program_channel = (string)$pr['channel'];
			
			$categories = array();
			
			foreach ($pr->category as $cat) {
				$categories[] = (string)$cat;
			}
			
			$program->program_category = implode('|', $categories);
			
			$program->write();
			
			$pI++;
		}
		
		return sprintf('Imported %d channels with %d programs.', $chI, $pI);
	}
	
	public function woef() {
		/*$test = new VCondition(VCondition::CONDITION_GT, 10);
		$test2 = new VCondition(VCondition::CONDITION_LT, 35);
		$test3 = new VCondition(VCondition::CONDITION_IN, array(1, 5));
		
		$test4 = new VCondition(VCondition::CONDITION_AND, array($test, $test2));
		$test5 = new VCondition(VCondition::CONDITION_OR, array($test4, $test3));
		
		die($test5->to_sql_string('numtje', 'int'));*/
		
		$endAfter = new VCondition(VCondition::CONDITION_GTE, time());
		$startBefore = new VCondition(VCondition::CONDITION_LTE, time() + 7200);

		$program = new Program();
		$program->program_end = $endAfter;
		$program->program_start = $startBefore;
		
		var_dump($program->read());
		
		exit;
	}
}