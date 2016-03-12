# WSDL-Dateien für SOAP-Schnittstelle der Fritzbox

Für die Beispiele werden die [SOAP-Klasse](http://php.net/soap) von PHP verwendet.

## Informationen zur Fritzbox auslesen

    $client = new SoapClient('wsdl/DeviceInfo1.wsdl', [
        'login' => FRITZBOX_USERNAME,
        'password' => FRITZBOX_PASSWORD
    ]);
    var_dump($client->GetInfo());

    array(12) {
      'NewManufacturerName' =>
      string(3) "AVM"
      'NewManufacturerOUI' =>
      string(6) "00040E"
      'NewModelName' =>
      string(20) "FRITZ!Box 6490 Cable"
      'NewDescription' =>
      string(30) "FRITZ!Box 6490 Cable 141.06.30"
      'NewProductClass' =>
      string(9) "FRITZ!Box"
      'NewSerialNumber' =>
      string(12) "xxxxxxxxxxx"
      'NewSoftwareVersion' =>
      string(9) "141.06.30"
      'NewHardwareVersion' =>
      string(20) "FRITZ!Box 6490 Cable"
      'NewSpecVersion' =>
      string(3) "1.0"
      'NewProvisioningCode' =>
      string(0) ""
      'NewUpTime' =>
      int(267191)
      'NewDeviceLog' =>
      string(0) ""
    }

## Status und Statistiken

    $client = new SoapClient('wsdl/LANEthernetInterfaceConfig1.wsdl', [
        'login' => FRITZBOX_USERNAME,
        'password' => FRITZBOX_PASSWORD
    ]);

    var_dump($client->GetInfo());
    array(5) {
      'NewEnable' =>
      bool(true)
      'NewStatus' =>
      string(2) "Up"
      'NewMACAddress' =>
      string(17) "xx:xx:xx:xx:xx:xx"
      'NewMaxBitRate' =>
      string(4) "Auto"
      'NewDuplexMode' =>
      string(4) "Auto"
    }

    var_dump($client->GetStatistics());
    array(4) {
      'NewBytesSent' =>
      int(513252582)
      'NewBytesReceived' =>
      int(299893368)
      'NewPacketsSent' =>
      int(1198196)
      'NewPacketsReceived' =>
      int(1635265)
    }

## Linkgeschwindigkeit

    $client = new SoapClient('wsdl/WANCommonInterfaceConfig1.wsdl', [
        'login' => FRITZBOX_USERNAME,
        'password' => FRITZBOX_PASSWORD
    ]);
    var_dump($client->GetCommonLinkProperties());

    array(4) {
      'NewWANAccessType' =>
      string(14) "X_AVM-DE_Cable"
      'NewLayer1UpstreamMaxBitRate' =>
      int(15514000)
      'NewLayer1DownstreamMaxBitRate' =>
      int(261120000)
      'NewPhysicalLinkStatus' =>
      string(2) "Up"
    }

## Hosts auslesen

    $client = new SoapClient('wsdl/Hosts1.wsdl', [
        'login' => FRITZBOX_USERNAME,
        'password' => FRITZBOX_PASSWORD
    ]);

    $hostCount = $client->GetHostNumberOfEntries();
    for($i = 0; $i < $hostCount; $i++) {
        var_dump($client->GetGenericHostEntry($i));
    }

    array(7) {
      'NewIPAddress' =>
      string(10) "10.0.1.124"
      'NewAddressSource' =>
      string(4) "DHCP"
      'NewLeaseTimeRemaining' =>
      int(0)
      'NewMACAddress' =>
      string(17) "xx:xx:xx:xx:xx:xx"
      'NewInterfaceType' =>
      string(8) "Ethernet"
      'NewActive' =>
      bool(true)
      'NewHostName' =>
      string(13) "RaspberryPIv3"
    }
    array(7) {
      'NewIPAddress' =>
      string(9) "10.0.1.11"
      'NewAddressSource' =>
      string(4) "DHCP"
      'NewLeaseTimeRemaining' =>
      int(31535743)
      'NewMACAddress' =>
      string(17) "xx:xx:xx:xx:xx:xx"
      'NewInterfaceType' =>
      string(8) "Ethernet"
      'NewActive' =>
      bool(true)
      'NewHostName' =>
      string(14) "Nintendo-Wii-U"
    }

