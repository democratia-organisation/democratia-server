<?php

// TODO : pour implémenter le query route
/**
 * [<queryPart>] => [
 * "<Method>" => [{"{:}<paremtre>"}, {parametersArray}, "<sql request>", ]
 * ]
 *
 * exemple :
 * http://localhost/user/500 Method: GET
 * on utilise $queryTable["user"]["GET"] qui contient : [500, "SELECT * FROM internaute WHERE id_internaute=?"]
 *
 * http://localhost/groupes Method: GET
 * on utilise $queryTable["groupes"]["GET"] qui contient : ["SELECT * FROM groupe"]
 * * http://localhost/users Method: GET
 * on utilise $queryTable["users"]["GET"] qui contient : [parameters, "SELECT * FROM internaute LIMIT ?*?"]
 */
$array = [

];
