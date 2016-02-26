<?php

use Meng\Soap\Interpreter;

class InterpreterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function requestWithWsdl()
    {
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $request = $interpreter->request('ConversionRate', [['FromCurrency' => 'AFA', 'ToCurrency' => 'ALL']]);
        $this->assertEquals('http://www.webservicex.net/CurrencyConvertor.asmx', $request->getEndpoint());
        $this->assertEquals('http://www.webserviceX.NET/ConversionRate', $request->getSoapAction());
        $this->assertEquals('1', $request->getSoapVersion());
        $this->assertNotEmpty($request->getSoapMessage());
        $this->assertContains('http://schemas.xmlsoap.org/soap/envelope/', $request->getSoapMessage());
        $this->assertContains('ConversionRate', $request->getSoapMessage());
        $this->assertContains('FromCurrency', $request->getSoapMessage());
        $this->assertContains('ToCurrency', $request->getSoapMessage());
    }

    /**
     * @test
     */
    public function responseWithWsdl()
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <m:Trans xmlns:m="http://www.w3schools.com/transaction/" soap:mustUnderstand="1">
      234
    </m:Trans>
  </soap:Header>
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/CurrencyConvertor.asmx?WSDL');
        $outputHeaders = [];
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate', $outputHeaders);
        $this->assertEquals(['ConversionRateResult' => '-1'], (array)$responseMessage);
        $this->assertNotEmpty($outputHeaders);
    }

    /**
     * @test
     */
    public function requestWithWsdlSoapV12()
    {
        $interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL', ['soap_version' => SOAP_1_2]);
        $request = $interpreter->request('GetAirportInformationByCountry', [['country' => 'United Kingdom']]);
        $this->assertEquals('http://www.webservicex.net/airport.asmx', $request->getEndpoint());
        $this->assertEquals('http://www.webserviceX.NET/GetAirportInformationByCountry', $request->getSoapAction());
        $this->assertEquals('2', $request->getSoapVersion());
        $this->assertNotEmpty($request->getSoapMessage());
        $this->assertContains('http://www.w3.org/2003/05/soap-envelope', $request->getSoapMessage());
        $this->assertContains('GetAirportInformationByCountry', $request->getSoapMessage());
        $this->assertContains('country', $request->getSoapMessage());
    }

    /**
     * @test
     */
    public function responseWithWsdlSoapV12()
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
            <GetAirportInformationByCountryResult>&lt;NewDataSet /&gt;</GetAirportInformationByCountryResult>
        </GetAirportInformationByCountryResponse>
    </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter('http://www.webservicex.net/airport.asmx?WSDL', ['soap_version' => SOAP_1_2]);
        $responseMessage = $interpreter->response($responseMessage, 'GetAirportInformationByCountry');
        $this->assertEquals(['GetAirportInformationByCountryResult' => '<NewDataSet />'], (array)$responseMessage);
    }

    /**
     * @test
     */
    public function requestWithoutWsdl()
    {
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com']);
        $request = $interpreter->request('anything', [['one' => 'two', 'three' => 'four']]);
        $this->assertEquals('www.location.com', $request->getEndpoint());
        $this->assertEquals('www.uri.com#anything', $request->getSoapAction());
        $this->assertEquals('1', $request->getSoapVersion());
        $this->assertContains('one', $request->getSoapMessage());
        $this->assertContains('two', $request->getSoapMessage());
        $this->assertContains('three', $request->getSoapMessage());
        $this->assertContains('four', $request->getSoapMessage());
    }

    /**
     * @test
     */
    public function responseWithoutWsdl()
    {
        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <GetAirportInformationByCountryResponse xmlns="http://www.webserviceX.NET">
            <GetAirportInformationByCountryResult>&lt;NewDataSet /&gt;</GetAirportInformationByCountryResult>
        </GetAirportInformationByCountryResponse>
    </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com', 'soap_version' => SOAP_1_2]);
        $responseMessage = $interpreter->response($responseMessage, 'GetAirportInformationByCountry');
        $this->assertEquals('<NewDataSet />', $responseMessage);

        $responseMessage = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <m:Trans xmlns:m="http://www.w3schools.com/transaction/" soap:mustUnderstand="1">
      234
    </m:Trans>
  </soap:Header>
  <soap:Body>
    <ConversionRateResponse xmlns="http://www.webserviceX.NET/">
      <ConversionRateResult>-1</ConversionRateResult>
    </ConversionRateResponse>
  </soap:Body>
</soap:Envelope>
EOD;
        $interpreter = new Interpreter(null, ['uri'=>'www.uri.com', 'location'=>'www.location.com']);
        $outputHeaders = [];
        $responseMessage = $interpreter->response($responseMessage, 'ConversionRate', $outputHeaders);
        $this->assertEquals('-1', $responseMessage);
        $this->assertNotEmpty($outputHeaders);
    }
}
