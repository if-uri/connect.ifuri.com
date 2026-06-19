<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/hub.php';

hub_send_json(hub_search_index());
