<?php
namespace watoki\stores\sqlite\serializers;

class DateTimeSerializer extends \watoki\stores\sql\serializers\DateTimeSerializer {

    protected function getColumnDefinition() {
        return 'TEXT(32)';
    }

}