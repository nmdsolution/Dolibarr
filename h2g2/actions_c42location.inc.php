<?php
/*
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
 * Copyright (C) 2019-2020 Hugo ALLEGAERT           <hugo@code42.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * $action, $object, $location must be set before including this file
 *
 * $action is the action on card
 * $object is the object of the card
 * $location is a c42Location which as to be fetch
 */
$langs->load('h2g2@h2g2');
if ($object->id) {
    // Edit a location
    if ($action == 'confirmEditMap' && $location->rowid) {
        $roadNbr = GETPOST('roadNbr');
        $roadName = GETPOST('roadName');
        $city = GETPOST('city');
        $zip = GETPOST('zip');
        $country = GETPOST('country');

        $geocode = $location->getGeoCode($roadNbr . '+' . $roadName . '+' . $city . '+' . $zip . '+' . $country);
        $location->road_nb = $roadNbr;
        $location->road_name = $roadName;
        $location->city = $city;
        $location->zip = $zip;
        $location->country = $country;
        $location->latitude = $geocode['lat'];
        $location->longitude = $geocode['lon'];
        $res = $location->update($user);
        if ($res > 0) {
            setEventMessages($langs->trans('LocationModified'), '', 'mesgs');
        } else {
            setEventMessages($langs->trans('LocationNotModified'), '', 'errors');
        }
        exit(header('Location:'.$_SERVER['PHP_SELF'].'?id='.$object->id));
    }
    // Create a location
    if ($action == 'confirmCreateMap') {
        $roadNbr = GETPOST('roadNbr');
        $roadName = GETPOST('roadName');
        $city = GETPOST('city');
        $zip = GETPOST('zip');
        $country = GETPOST('country');

        $geocode = $location->getGeoCode($roadNbr . '+' . $roadName . '+' . $city . '+' . $zip . '+' . $country);
        $location->road_nb = $roadNbr;
        $location->road_name = $roadName;
        $location->city = $city;
        $location->zip = $zip;
        $location->country = $country;
        $location->latitude = $geocode['lat'];
        $location->longitude = $geocode['lon'];
        $res = $location->create($user);
        if ($res > 0) {
            // Link location to the object
            if ($object->linkC42Location($user, $res) > 0) {
                setEventMessages($langs->trans('LocationCreated'), '', 'mesgs');
            } else {
                setEventMessages($langs->trans('LocationCreatedButNotLinked'), '', 'errors');
            }
        } else {
            setEventMessages($langs->trans('LocationNotCreated'), '', 'errors');
        }
        exit(header('Location:'.$_SERVER['PHP_SELF'].'?id='.$object->id));
    }
    // Delete location
    if ($action == 'confirm_delete_location') {
        $confirm = GETPOST('confirm');
        if ($confirm == 'yes') {
            $res = $location->delete($user);
            if ($res > 0 && $object->unlinkC42Location($user) > 0) {
                setEventMessages($langs->trans('LocationDelete'), '', 'mesgs');
            } else {
                setEventMessages($langs->trans('ErrorLocationDelete'), '', 'mesgs');
            }
        }
        exit(header('Location:'.$_SERVER['PHP_SELF'].'?id='.$object->id));
    }
}