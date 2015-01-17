<?php
namespace watoki\stores\sqlite\serializers;

class DateTimeImmutableSerializer extends \watoki\stores\sql\serializers\DateTimeImmutableSerializer {

    protected function getColumnDefinition() {
        return 'TEXT(32)';
    }


}