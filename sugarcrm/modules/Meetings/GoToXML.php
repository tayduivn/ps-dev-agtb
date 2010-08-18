<?php

$login_xml = <<<LOG
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:impl="G2M_Organizers">
  <soap:Body
soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <impl:logon>
      <id xsi:type="xsd:string"></id>
      <password xsi:type="xsd:string"></password>
      <version xsi:type="xsd:long">2</version>
    </impl:logon>
  </soap:Body>
</soap:Envelope>
LOG;

$schedule_xml = <<<SCH
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:impl="G2M_Organizers">
  <soap:Body soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <impl:createMeeting>
      <connectionId xsi:type="xsd:string"></connectionId>
      <meetingParameters xsi:type="impl:MeetingParameters">
        <subject xsi:type="xsd:string"></subject>
        <startTime xsi:type="xsd:dateTime"></startTime>
        <timeZoneKey xsi:type="xsd:string">50</timeZoneKey>
        <conferenceCallInfo xsi:type="xsd:string">Free</conferenceCallInfo>
        <meetingType xsi:type="xsd:string">Scheduled</meetingType>
        <passwordRequired xsi:type="xsd:boolean"></passwordRequired>
      </meetingParameters>
    </impl:createMeeting>
  </soap:Body>
</soap:Envelope>
SCH;
