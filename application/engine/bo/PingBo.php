<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

	This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

class PingBo {
	var $pdo = null;
	var $config = null;
	var $galetteDatabase = "";

	var $TABLE = "pings";
	var $ID_FIELD = "pin_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->galetteDatabase = $config["galette"]["db"] . ".";

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config) {
		return new PingBo($pdo, $config);
	}

	function create(&$ping) {
		$query = "	INSERT INTO $this->TABLE () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//				echo showQuery($query, $args);

		try {
			$statement->execute();
			$ping[$this->ID_FIELD] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($ping) {
		$query = "	UPDATE $this->TABLE SET ";

		$separator = "";
		foreach($ping as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

//		echo showQuery($query, $ping);

		$statement = $this->pdo->prepare($query);
		$statement->execute($ping);
	}

	function save(&$ping) {
 		if (!isset($ping[$this->ID_FIELD]) || !$ping[$this->ID_FIELD]) {
			$this->create($ping);
		}

		$this->update($ping);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

		$queryBuilder->join($this->galetteDatabase."galette_adherents", "id_adh = pin_member_id", null, "left");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["pin_member_id"])) {
			$args["pin_member_id"] = $filters["pin_member_id"];
			$queryBuilder->where("pin_member_id = :pin_member_id");
		}

		if (isset($filters["pin_guest_id"])) {
			$args["pin_guest_id"] = $filters["pin_guest_id"];
			$queryBuilder->where("pin_guest_id = :pin_guest_id");
		}

		if (isset($filters["pin_meeting_id"])) {
			$args["pin_meeting_id"] = $filters["pin_meeting_id"];
			$queryBuilder->where("pin_meeting_id = :pin_meeting_id");
		}

		if (isset($filters["pin_speaking"])) {
			$args["pin_speaking"] = $filters["pin_speaking"];
			$queryBuilder->where("pin_speaking = :pin_speaking");
		}

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$index][$field]);
					}
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $results;
	}
}