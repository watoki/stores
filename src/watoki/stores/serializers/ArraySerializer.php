<?php
namespace watoki\stores\serializers;

use watoki\stores\Serializer;
class ArraySerializer implements Serializer{
	public function serialize($inflated) {
		return json_encode($inflated);

	}

	public function inflate($serialized) {
		return !!$serialized;
	}

	public function getDefinition() {
		return 'ARRAY';
	}
} 