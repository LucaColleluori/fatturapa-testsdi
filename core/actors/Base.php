<?php


namespace FatturaPa\Core\Actors;

use DateInterval;
use DateTime;
use Exception;
use FatturaPa\Core\Models\Channel;
use FatturaPa\Core\Models\Database;
use FatturaPa\Core\Models\Notification;
use InvalidArgumentException;
use URL;

define('TIME_TRAVEL_DB', __DIR__ . '/../../core/storage/time_travel.json');

class Base
{

    private static function persist($data)
    {
        file_put_contents(TIME_TRAVEL_DB, json_encode($data));
    }

    public static function retrieve()
    {
        $data = json_decode(file_get_contents(TIME_TRAVEL_DB), true);
        $data['real_time'] = DateTime::__set_state($data['real_time']);
        $data['simulated_time'] = DateTime::__set_state($data['simulated_time']);

        return $data;
    }

    public static function resetTime()
    {
        $data = array(
            'real_time'      => new DateTime(),
            'simulated_time' => new DateTime(),
            'speed'          => 1.0,
        );
        self::persist($data);
    }

    public static function setDateTime($datetime)
    {
        $data = self::retrieve();
        $data['real_time'] = new DateTime();
        $data['simulated_time'] = $datetime;
        self::persist($data);
    }

    public static function setSpeed($speed)
    {
        self::getDateTime();
        $data = self::retrieve();
        $data['speed'] = $speed;
        self::persist($data);
    }

    public static function getDateTime()
    {
        $data = self::retrieve();
        $real_time_now = new DateTime();

        $delta_seconds = round(($real_time_now->getTimestamp() - $data['real_time']->getTimestamp()) * $data['speed']);
        $simulated_time_now = $data['simulated_time']->add(new DateInterval("PT${delta_seconds}S"));
        $data['real_time'] = $real_time_now;
        $data['simulated_time'] = $simulated_time_now;
        self::persist($data);

        return $data['simulated_time'];
    }

    private static function notification($notification_blob, $filename, $type, $invoice_id, $status)
    {
        new Database();
        $dateTime = Base::getDateTime();
        $Notification = Notification::create(
            [
                'invoice_id' => $invoice_id,
                'type'       => $type,
                'status'     => $status,
                'blob'       => $notification_blob,
                'actor'      => Base::getActor(),
                'nomefile'   => $filename,
                'ctime'      => $dateTime->date,
            ]
        );

        return $Notification;
    }

    public static function receive($notification_blob, $filename, $type, $invoice_id)
    {
        self::notification($notification_blob, $filename, $type, $invoice_id, 'N_RECEIVED');
    }

    public static function enqueue($notification_blob, $filename, $type, $invoice_id)
    {
        Notification::where('status', '=', 'N_PENDING')
            ->where('invoice_id', '=', $invoice_id)
            ->update(array('status' => 'N_OBSOLETE'));
        self::notification($notification_blob, $filename, $type, $invoice_id, 'N_PENDING');
    }

    public static function dispatchNotification($service, $addressee, $endpoint, $operation, $fileSdI)
    {
        echo 'dispatchNotification to: ' . $addressee . '<br/>';
        $service->__setLocation(HOSTMAIN . $addressee . "/soap/$endpoint/");
        $sent = false;
        try {
            $service->$operation($fileSdI);
            $sent = true;
        } catch (SoapFault $e) {
            echo "SOAP Fault: (faultcode: {" . $e->faultcode . "}, faultstring: {" . $e->faultstring . "})";
            exit;
        }

        return $sent;
    }

    public static function getActor()
    {
        new Database();
        if (class_exists('\URL')) {
            // we're inside Laravel: URL is defined in rpc/config/app.php
            $url = URL::current();
            $urlData = explode("/", $url);
            $actor = @$urlData[3];
        } else {
            $url = $_SERVER['REQUEST_URI'];
            $urlData = explode("/", $url);
            $actor = $urlData[1];
        }

        $issuers = self::getActors();
        if (!in_array($actor, $issuers)) {
            abort(404);
        }

        return $actor;
    }

    public static function getIssuers()
    {
        $channels = collect();
        try {
            $channels = Channel::select(['issuer'])->distinct()->get();
        } catch (Exception $ex) {

        }
        $issuers = [];
        foreach ($channels->toArray() as $channel) {
            $issuers[] = $channel['issuer'];
        }

        return $issuers;
    }

    public static function getActors()
    {
        $channels = collect();
        try {
            $channels = Channel::select(['issuer'])->distinct()->get();
        } catch (Exception $ex) {
        }
        $actors = array('sdi');
        foreach ($channels->toArray() as $channel) {
            $actors[] = "td" . $channel['issuer'];
        }

        return $actors;
    }

    public static function getChannels()
    {
        $Channelslist = Channel::all();

        $channels = array();
        foreach ($Channelslist as $k => $channel) {
            $channels[$channel['cedente']] = $channel['issuer'];
        }

        return $channels;
    }

    public static function unpack($xmlString)
    {
        // defend against XML External Entity Injection
        libxml_disable_entity_loader(true);
        $collapsed_xml_string = preg_replace("/\s+/", "", $xmlString);
        $collapsed_xml_string = $collapsed_xml_string ? $collapsed_xml_string : $xmlString;
        if (preg_match("/\<!DOCTYPE/i", $collapsed_xml_string)) {
            throw new InvalidArgumentException('Invalid XML: Detected use of illegal DOCTYPE');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOWARNING);
        if ($xml === false) {
            throw new InvalidArgumentException("Cannot load XML\n");
        }

        return $xml;
    }
}
