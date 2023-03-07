<?php

namespace SagarmathaAPIPostImport;

class API
{
	private const OBJECTS_API = 'https://xlibris.public.prod.oc.inl.infomaker.io:8443/opencontent/objects/';
	private const SEARCH_API = 'https://xlibris.public.prod.oc.inl.infomaker.io:8443/opencontent/';

	public static function object_api(){
		return self::OBJECTS_API;

	}
	public static function search_api(){
		return self::SEARCH_API;
		
	}

}