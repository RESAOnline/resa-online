<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

interface RESA_InterfaceDTO
{
	/**
	 * return table name
	 */
	public static function getTableName();

	/**
	 * return the create query
	 */
	public static function getCreateQuery();

	/**
	 * return the create query
	 */
	public static function getConstraints();

	/**
	 * return the delete query
	 */
	public static function getDeleteQuery();

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array());

	/**
	 * Load form database
	 */
	public function loadById($id);

	/**
	 * Save in database
	 */
	public function save();

	/**
	 * Delete in database
	 */
	public function deleteMe();

}

interface RESA_InterfaceJSON
{
	/**
	 * Return this to JSON value
	 */
	public function toJSON();

	/**
	 * load object with json
	 */
	public function fromJSON($json);
}


abstract class RESA_EntityDTO implements RESA_InterfaceDTO, RESA_InterfaceJSON
{
	protected $linkWPDB;
	protected $loaded;


	public function __construct()
	{
		global $wpdb;
		$this->linkWPDB = $wpdb;
		$this->loaded = false;
	}

	public function setLoaded($loaded)
	{
		$this->loaded = $loaded;
	}

	public function isLoaded()
	{
		return $this->loaded;
	}

	public function toRealJSON(){
		return json_decode($this->toJSON());
	}


}
