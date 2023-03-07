<?php

namespace SagarmathaAPIPostImport;

interface ClassConstant
{

	/*** APIs **/
	//Stage
	//public const OBJECTS_API = 'https://xlibris.public.stage.oc.inl.infomaker.io:8443/opencontent/objects/';
	//public const SEARCH_API = 'https://xlibris.public.stage.oc.inl.infomaker.io:8443/opencontent/';
	//public const EDITORIAL_SEARCH_API = 'https://xlibris.editorial.stage.oc.inl.infomaker.io:8443/opencontent/';
	//PROD
	public const OBJECTS_API = 'https://xlibris.public.prod.oc.inl.infomaker.io:8443/opencontent/objects/';
	public const SEARCH_API = 'https://xlibris.public.prod.oc.inl.infomaker.io:8443/opencontent/';
	public const EDITORIAL_SEARCH_API = 'https://xlibris.editorial.prod.oc.inl.infomaker.io:8443/opencontent/';
	//ANA Synd
	//public const OBJECTS_API = 'https://xlibris.syndication.prod.oc.inl.infomaker.io:8443/opencontent/objects/';
	//public const SEARCH_API = 'https://xlibris.syndication.prod.oc.inl.infomaker.io:8443/opencontent/';


	/*** Credintials  TODO: make this configs ***/
	//stage
	//public const USER = 'admin';
	//public const PASS = '7KM.2etsTlFt';
	//PROD
	public const USER = 'devadmin';
	public const PASS = 'wdQxm4evNYmK';
	//ANA Synd
	//public const USER = 'devadmin';
	//public const PASS = 'AsUhg8aDqekCg01z';
	//EDITORIAL
	public const ED_USER = 'inl';
	public const ED_PASS = 'RgfJF7KYeJDv';


	/*** Event Log API ***/
	public const LOG_API = 'https://xlibris.public.prod.oc.inl.infomaker.io:8443/opencontent/eventlog?event=';

	/*** Event log file ***/
	public const CSV_LOGFILE = ABSPATH . 'wp-content/plugins/sagarmatha-api-post-import/logs/eventLog.csv';


	/*** Imengine API ***/
	//staging api
	//public const IMENGINE_SERVER = 'https://imengine.public.stage.inl.infomaker.io';
	//public api
	public const IMENGINE_SERVER = 'https://imengine.public.prod.inl.infomaker.io';
	//syndication api
	//public const IMENGINE_SERVER = 'https://imengine.syndication.prod.inl.infomaker.io';

	/*** Channel ***/
	public const CHANNEL = array('Northern News');

	/*** Limit for amount of articles to query ***/
	public const LIMIT = '40'; 

	/*** Amount of time backwards from current time ***/
	public const PAST_TIME = '- 300 minutes'; 

}