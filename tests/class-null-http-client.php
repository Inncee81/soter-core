<?php

class Null_Http_Client implements Soter_Core\Http_Interface {
	public function get( $url ) {
		return [ 0, [], '' ];
	}
}
