<?php
   /**
   * Parser
   *
   * Парсер Русгидро
   *
   */
namespace gidro;

include_once 'vendor/autoload.php';

use simplehtmldom\HtmlWeb;

class ges {			
	
	private $url = 'http://www.rushydro.ru/hydrology/informer/';
	
	public $set = [
		'zeya' => 'ГЭС на реке Зея',
		'bureya' => 'ГЭС на реке Бурея',
		'kolyma' => 'ГЭС на реке Колыма',
		'uglich' => 'Угличиская ГЭС',
		'rybinsk' => 'Рыбинская ГЭС',
		'nijegorod' => 'Нижегородская ГЭС',
		'cheboksar' => 'Чебоксарская ГЭС',
		'jigul' => 'Жигулевская ГЭС',
		'saratov' => 'Саратовская ГЭС',
		'voljsk' => 'Волжская ГЭС',
		'kama' => 'Камская ГЭС',
		'votinsk' => 'Воткинская ГЭС',
		'sayano' => 'Саяно-Шушенская ГЭС',
		'irkutsk' => 'Иркутская ГЭС',
		'bratsk' => 'Братская ГЭС',
		'ust-ilim' => 'Усть-Илимская ГЭС',
		'boguch' => 'Богучанская ГЭС',
		'novosib' => 'Новосибирская ГЭС',
		'irganai' => 'Ирганайская ГЭС',
		'chirkeysk' => 'Чиркейская ГЭС',
		'vilyuj' => 'Вилюйская ГЭС'		
	];
	
	private $legend = [
		'fpu' => ['name'=>'ФПУ - форсированный подпорный уровень','comment'=>'максимальная технически возможная отметка наполнения водохранилища','metric'=>'м'],
		'npu' => ['name'=>'НПУ - нормальный подпорный уровень','comment'=>'отметка полного наполнения водохранилища в обычных условиях','metric'=>'м'],
		'umo' => ['name'=>'УМО - уровень мертвого объема','comment'=>'отметка предельной сработки водохранилища','metric'=>'м'],
		'current' => ['name'=>'Уровень','comment'=>'текущая отметка уровня воды в водохранилище на 8:00 (МСК)','metric'=>'м'],
		'volume' => ['name'=>'Свободная ёмкость','comment'=>'свободный объем водохранилища, разница между текущим уровнем и НПУ','metric'=>'млн.куб.м'],
		'inflow' => ['name'=>'Приток','comment'=>'количество воды, поступившей в водохранилище за предыдущие сутки (среднесуточное значение)','metric'=>'куб.м/с'],
		'expend' => ['name'=>'Общий расход','comment'=>'общее количество воды, пропускаемой через гидроузел (турбины и водосбросы) за предыдущие сутки','metric'=>'куб.м/с'],
		'dump' => ['name'=>'Расход через водосбросы','comment'=>'количество воды, сбрасываемой через водосбросы мимо турбин за предыдущие сутки (среднесуточное значение)','metric'=>'куб.м/с'],
	];
	
	//результаты разбора
	public $result;
	
	public function __construct($name=NULL) {	
		if (!empty($name)) return $this->get($name);
	}	
	
	//парсер
	protected function parse() {
		$client = new HtmlWeb();
		return $client->load($this->url);
	}
	
	/**
	* Получение данных
	* 
	* @param string $name Индентификатор ГЭС (см $set). При отсустствии - текущие данные для всех ГЭС
	* @return object ges
	*/
	public function get($name=NULL) {		
		$result = $this->get_all();
		if (empty($name)) {
			$this->result = $result; 
		} else {
			$this->result = (isset($result[$name])) ? $result[$name] : NULL;
		}
		return $this;
	}
	
	public function get_all() {
		$result = [];
		$html = $this->parse();
		$blocks = $html->find('.data-block');
		foreach ($blocks as $block) {
			foreach ($this->set as $key=>$value) {				
				$html_data = $block->find('.'.$key);
				if (!empty($html_data)) {				
					$result[$key] = new \STDClass;
					$result[$key]->name = $value;
					$result[$key]->dataset = $this->data_parser($html_data[0]);				
				}
			}
		}
		return $result;
	}
	
	private function data_parser($html) {
		$result = new \STDClass;
		$legend_data = $this->legend_parser($html->find('.legend',0));
		foreach ($this->legend as $key=>$value) {			
			$html_data = $html->find('.'.$key,0);			
			$result->$key = new \STDClass;
			$result->$key->legend = (object) $value;
			if (!empty($html_data)) {				
				$result->$key->data = (float) $html_data->plaintext;				
			} else {
				$result->$key->data = (isset($legend_data[$key])) ? $legend_data[$key] : NULL;
			}
		}
		return $result;
	}
	
	private function legend_parser($html) {
		$result = array();		
		$ps = $html->find("p");	
		foreach ($ps as $p) {
			foreach ($this->legend as $key=>$value) {				
				if (strpos($p->plaintext,$value['name'])!==FALSE) {	
					$result[$key] = (float) explode(' ',$p->find("b",0)->plaintext)[0];
				}
			}
		}
		return $result;
	}
	
}
