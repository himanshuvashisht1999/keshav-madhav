<?php

function getformatDateTime($dateString)
{
    return date('d M Y h:i A', strtotime($dateString));
}

function getformatDate($dateString)
{
    return date('d M Y', strtotime($dateString));
}



?>