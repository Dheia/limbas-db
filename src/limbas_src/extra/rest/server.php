<?php
/**
 * @copyright Limbas GmbH <https://limbas.com>
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */



use limbas\rest\ErrorHandler;
use limbas\rest\RestException;
use limbas\rest\Router;

header('Content-type: application/json; charset=utf-8');

require_once('classes/ErrorHandler.php');
require_once('classes/Request.php');
require_once('classes/RequestHandlers/RequestHandler.php');
require_once('classes/RequestHandlers/Delete/DeleteRequestHandler.php');
require_once('classes/RequestHandlers/Delete/DeleteDatasetRequestHandler.php');
require_once('classes/RequestHandlers/Delete/DeleteRelationRequestHandler.php');
require_once('classes/RequestHandlers/Get/GetRequestHandler.php');
require_once('classes/RequestHandlers/Get/GetTableRequestHandler.php');
require_once('classes/RequestHandlers/Get/GetDatasetRequestHandler.php');
require_once('classes/RequestHandlers/Get/GetRelationRequestHandler.php');
require_once('classes/RequestHandlers/Patch/PatchRequestHandler.php');
require_once('classes/RequestHandlers/Patch/PatchDatasetRequestHandler.php');
require_once('classes/RequestHandlers/Patch/PatchRelationRequestHandler.php');
require_once('classes/RequestHandlers/Post/PostRequestHandler.php');
require_once('classes/RequestHandlers/Post/PostTableRequestHandler.php');
require_once('classes/RequestHandlers/Post/PostRelationRequestHandler.php');
require_once('classes/Router.php');
require_once('classes/RestException.php');

require_once(COREPATH . 'gtab/gtab.lib');
require_once(COREPATH . 'gtab/gtab_type_erg.lib');

# TODO:
# Verknüpfungsobjekte: attributes
# Sonderfeldtypen
# Versionierung
# Delete auf ganze Tabelle nur mit Filter erlauben
# 1:1 table relation: später


# ?id=1
# ?id[eq]=1
# ?kontaktetest[id]=1
# ?kontaktetest[id][eq]=1





try {
    ErrorHandler::registerShutdownHandler();

    $router = new Router();
    $request = $router->getRequest();
    $requestHandler = $request->getHandler();
    $answer = $requestHandler->getAnswer();
    $json = json_encode($answer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (!$json) {
        throw new RestException('Internal server error', 500);
    }

    ErrorHandler::unregisterShutdownHandler();
    ErrorHandler::checkLmbLog();
    $requestHandler->sendHeader();
    echo $json;

} catch (RestException $e) {
    ErrorHandler::unregisterShutdownHandler();
    # TODO include log
    ErrorHandler::t($e);
}
