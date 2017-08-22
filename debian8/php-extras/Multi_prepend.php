<?php

foreach (explode(',', getenv('MULTI_PREPEND')) as $value) {
    require_once(trim($value));
}
