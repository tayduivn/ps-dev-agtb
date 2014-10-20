<?php


interface PMSEObservable
{
    public function attach($observer);
    public function detach($observer);
    public function notify();
}
