<?php

# https://avm.de/service/schnittstellen/

define('WSDL_NAMESPACE', 'http://schemas.xmlsoap.org/wsdl/');
define('SOAP_NAMESPACE', 'http://schemas.xmlsoap.org/wsdl/soap/');
# define('XSD_NAMESPACE', 'http://www.w3.org/2001/XMLSchema');

function generateWsdl(string $url, string $endpoint, SimpleXMLElement $service) {
	$name = parseName($service->serviceType);

	$content = file_get_contents($url);
	$xml2 = simplexml_load_string($content);

	$document = new DOMDocument();
	$document->formatOutput = TRUE;

	$definitions = $document->createElementNS(WSDL_NAMESPACE, 'definitions');
	$document->appendChild($definitions);

	$document->createAttributeNS(SOAP_NAMESPACE, 'soap:TEMP');
	$document->createAttributeNS(XSD_NAMESPACE, 'xsd:TEMP');

	$serviceElement = $document->createElementNS(WSDL_NAMESPACE, 'service');
	$serviceElement->setAttribute('name', $name);
	$definitions->appendChild($serviceElement);

	$binding = $document->createElementNS(WSDL_NAMESPACE, 'binding');
	$binding->setAttribute('name', $name);
	$binding->setAttribute('type', $name);
	$definitions->appendChild($binding);

	$element = $document->createElementNS(SOAP_NAMESPACE, 'binding');
	$element->setAttribute('style', 'rpc');
	$element->setAttribute('transport', 'http://schemas.xmlsoap.org/soap/http');
	$binding->appendChild($element);

	$portType = $document->createElementNS(WSDL_NAMESPACE, 'portType');
	$portType->setAttribute('name', $name);
	$definitions->appendChild($portType);

    $port = $document->createElementNS(WSDL_NAMESPACE, 'port');
    $port->setAttribute('binding', $name);
    # $port->setAttribute('name', 'default');
    $serviceElement->appendChild($port);

    $address = $document->createElementNS(SOAP_NAMESPACE, 'address');
    $address->setAttribute('location', $endpoint);
    $port->appendChild($address);

    foreach($xml2->actionList->action as $action) {
        $actionName = (string) $action->name;
        $actionName = strtr($actionName, [
            'X_AVM-DE_' => '',
            'X_AVM_DE_' => ''
        ]);
        $actionName = lcfirst($actionName);

		$operation = $document->createElementNS(WSDL_NAMESPACE, 'operation');
		$operation->setAttribute('name', $actionName);
		$binding->appendChild($operation);

		$element = $document->createElementNS(SOAP_NAMESPACE, 'operation');
		$element->setAttribute('soapAction', $service->serviceType . '#' . $action->name);
		$operation->appendChild($element);

		$input = $document->createElementNS(WSDL_NAMESPACE, 'input');
		# $operation->appendChild($input);

		$body = $document->createElementNS(SOAP_NAMESPACE, 'body');
		$body->setAttribute('use', 'encoded');
		$body->setAttribute('namespace', (string) $service->serviceType);
		$body->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$input->appendChild($body);

		$output = $document->createElementNS(WSDL_NAMESPACE, 'output');
		# $operation->appendChild($output);

		$body = $document->createElementNS(SOAP_NAMESPACE, 'body');
		$body->setAttribute('use', 'encoded');
		$body->setAttribute('namespace', (string) $service->serviceType);
		$body->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
		$output->appendChild($body);

		$operation = $document->createElementNS(WSDL_NAMESPACE, 'operation');
		$operation->setAttribute('name', $actionName);
		$portType->appendChild($operation);

		$input = $document->createElementNS(WSDL_NAMESPACE, 'input');
		$input->setAttribute('message', $actionName . 'Request');
		$operation->appendChild($input);

		$output = $document->createElementNS(WSDL_NAMESPACE, 'output');
		$output->setAttribute('message', (string) $actionName . 'Response');
		$operation->appendChild($output);

		$inputMessage = $document->createElementNS(WSDL_NAMESPACE, 'message');
		$inputMessage->setAttribute('name', $actionName . 'Request');
		$definitions->appendChild($inputMessage);

		$outputMessage = $document->createElementNS(WSDL_NAMESPACE, 'message');
		$outputMessage->setAttribute('name', $actionName . 'Response');
		$definitions->appendChild($outputMessage);

		foreach($action->argumentList->argument ?? [] as $argument) {
			$part = $document->createElementNS(WSDL_NAMESPACE, 'part');
			foreach($xml2->serviceStateTable->stateVariable as $stateVariable) {
				if((string) $stateVariable->name !== (string) $argument->relatedStateVariable) {
					continue;
				}
                $type = match((string) $stateVariable->dataType) {
                    'boolean' => 'xsd:boolean',
                    'i4' => 'xsd:int',
                    'ui1' => 'xsd:unsignedByte',
                    'ui2' => 'xsd:unsignedShort',
                    'ui4' => 'xsd:unsignedInt',
                    'string' => 'xsd:string',
                    'uuid' => 'xsd:string',
                    'dateTime' => 'xsd:dateTime',

                };
                $part->setAttribute('type', $type);
			}

			$part->setAttribute('name', (string) $argument->name);

			if((string) $argument->direction === 'in') {
				$inputMessage->appendChild($part);
			}

			if((string) $argument->direction === 'out') {
				$outputMessage->appendChild($part);
			}
		}

        if(!$inputMessage->hasChildNodes()) {
            $operation->removeChild($input);
            $definitions->removeChild($inputMessage);
        }
        if(!$outputMessage->hasChildNodes()) {
            $operation->removeChild($output);
            $definitions->removeChild($outputMessage);
        }
	}

	return $document->saveXML();
}

function parseName(string $serviceId): string {
	$parts = explode(':', $serviceId);
    $parts = array_slice($parts, -2);
    if($parts[1] === '1') {
        return $parts[0];
    }
	return implode('', array_slice($parts, -2));
}

function processService($service, string $path) {
	$name = parseName($service->serviceType);
    $name2 = strtr($name, [
        'X_AVM-DE_' => ''
    ]);
	printf("    Service: %s\n", $name);

	$url = 'http://fritz.box:49000' . $service->SCPDURL;
	$endpoint = 'http://fritz.box:49000' . $service->controlURL;

	$response = generateWsdl($url, $endpoint, $service);
	if(!file_exists($path)) {
		mkdir($path, 0777, true);
	}
    printf("    File: %s.wsdl\n", $name);
	file_put_contents($path.'/' . $name2 . '.wsdl', $response);
}

$urls = [
	'http://fritz.box:49000/MediaServerDevDesc-xbox.xml',
	'http://fritz.box:49000/MediaServerDevDesc.xml',
	'http://fritz.box:49000/avmnexusdesc.xml',
	'http://fritz.box:49000/fboxdesc.xml',
	'http://fritz.box:49000/igd2desc.xml',
	'http://fritz.box:49000/igddesc.xml',
    'http://fritz.box:49000/l2tpv3.xml',
	'http://fritz.box:49000/tr64desc.xml',
];

foreach($urls as $url) {
    printf("Url: %s\n", $url);
	$path = 'wsdl/'.pathinfo($url, PATHINFO_FILENAME);
    if(str_ends_with($path,  'desc')) {
        $path = substr($path, 0, -4);
    }
    try {
        $content = @file_get_contents($url);
        if(!$content) {
            continue;
        }
        $xml = simplexml_load_string($content);
    }
    catch(Throwable $e) {
        var_dump($e->getMessage());
        continue;
    }

	# var_dump((string) $content);
	# $xml->registerXPathNamespace('foo', 'urn:schemas-upnp-org:device-1-0');
	$xml->registerXPathNamespace('foo', $xml->getDocNamespaces()[""]);

    printf("Endpoint: %s\n", $path);

    foreach($xml->xpath('//foo:service') as $service) {
		processService($service, $path);
	}
}
