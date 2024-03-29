<?php

namespace Feeder\Service;

use Feeder\Cache\Cache;

/**
 * Geo related operations service
 *
 * @package Feeder\Service
 * @author  Michael BOUVY <michael.bouvy@clickandmortar.fr>
 */
class Geo
{
    /**
     * Version for AWS Location service
     *
     * @var string
     */
    const AWS_LOCATION_SERVICE_VERSION = '2020-11-19';

    /**
     * Countries filter for geocoding
     *
     * @var array
     */
    protected $countriesFilter = ['FRA'];

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Geo constructor.
     *
     * @param Cache    $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get (french) department name by code
     *
     * @param string $code Department code (2 characters)
     *
     * @return string
     */
    public function getDepartmentByCode($code)
    {
        $depts = array();
        $depts["01"] = "Ain (01)";
        $depts["02"] = "Aisne (02)";
        $depts["03"] = "Allier (03)";
        $depts["04"] = "Alpes de Haute Provence (04)";
        $depts["05"] = "Hautes Alpes (05)";
        $depts["06"] = "Alpes Maritimes (06)";
        $depts["07"] = "Ardèche (07)";
        $depts["08"] = "Ardennes (08)";
        $depts["09"] = "Ariège (09)";
        $depts["10"] = "Aube (10)";
        $depts["11"] = "Aude (11)";
        $depts["12"] = "Aveyron (12)";
        $depts["13"] = "Bouches du Rhône (13)";
        $depts["14"] = "Calvados (14)";
        $depts["15"] = "Cantal (15)";
        $depts["16"] = "Charente (16)";
        $depts["17"] = "Charente Maritime (17)";
        $depts["18"] = "Cher (18)";
        $depts["19"] = "Corrèze (19)";
        $depts["2A"] = "Corse du Sud (2A)";
        $depts["2B"] = "Haute Corse (2B)";
        $depts["21"] = "Côte d'Or (21)";
        $depts["22"] = "Côtes d'Armor (22)";
        $depts["23"] = "Creuse (23)";
        $depts["24"] = "Dordogne (24)";
        $depts["25"] = "Doubs (25)";
        $depts["26"] = "Drôme (26)";
        $depts["27"] = "Eure (27)";
        $depts["28"] = "Eure et Loir (28)";
        $depts["29"] = "Finistère (29)";
        $depts["30"] = "Gard (30)";
        $depts["31"] = "Haute Garonne (31)";
        $depts["32"] = "Gers (32)";
        $depts["33"] = "Gironde (33)";
        $depts["34"] = "Hérault (34)";
        $depts["35"] = "Ille et Vilaine (35)";
        $depts["36"] = "Indre (36)";
        $depts["37"] = "Indre et Loire (37)";
        $depts["38"] = "Isère (38)";
        $depts["39"] = "Jura (39)";
        $depts["40"] = "Landes (40)";
        $depts["41"] = "Loir et Cher (41)";
        $depts["42"] = "Loire (42)";
        $depts["43"] = "Haute Loire (43)";
        $depts["44"] = "Loire Atlantique (44)";
        $depts["45"] = "Loiret (45)";
        $depts["46"] = "Lot (46)";
        $depts["47"] = "Lot et Garonne (47)";
        $depts["48"] = "Lozère (48)";
        $depts["49"] = "Maine et Loire (49)";
        $depts["50"] = "Manche (50)";
        $depts["51"] = "Marne (51)";
        $depts["52"] = "Haute Marne (52)";
        $depts["53"] = "Mayenne (53)";
        $depts["54"] = "Meurthe et Moselle (54)";
        $depts["55"] = "Meuse (55)";
        $depts["56"] = "Morbihan (56)";
        $depts["57"] = "Moselle (57)";
        $depts["58"] = "Nièvre (58)";
        $depts["59"] = "Nord (59)";
        $depts["60"] = "Oise (60)";
        $depts["61"] = "Orne (61)";
        $depts["62"] = "Pas de Calais (62)";
        $depts["63"] = "Puy de Dôme (63)";
        $depts["64"] = "Pyrénées Atlantiques (64)";
        $depts["65"] = "Hautes Pyrénées (65)";
        $depts["66"] = "Pyrénées Orientales (66)";
        $depts["67"] = "Bas Rhin (67)";
        $depts["68"] = "Haut Rhin (68)";
        $depts["69"] = "Rhône (69)";
        $depts["70"] = "Haute Saône (70)";
        $depts["71"] = "Saône et Loire (71)";
        $depts["72"] = "Sarthe (72)";
        $depts["73"] = "Savoie (73)";
        $depts["74"] = "Haute Savoie (74)";
        $depts["75"] = "Paris (75)";
        $depts["76"] = "Seine Maritime (76)";
        $depts["77"] = "Seine et Marne (77)";
        $depts["78"] = "Yvelines (78)";
        $depts["79"] = "Deux Sèvres (79)";
        $depts["80"] = "Somme (80)";
        $depts["81"] = "Tarn (81)";
        $depts["82"] = "Tarn et Garonne (82)";
        $depts["83"] = "Var (83)";
        $depts["84"] = "Vaucluse (84)";
        $depts["85"] = "Vendée (85)";
        $depts["86"] = "Vienne (86)";
        $depts["87"] = "Haute Vienne (87)";
        $depts["88"] = "Vosges (88)";
        $depts["89"] = "Yonne (89)";
        $depts["90"] = "Territoire de Belfort (90)";
        $depts["91"] = "Essonne (91)";
        $depts["92"] = "Hauts de Seine (92)";
        $depts["93"] = "Seine St Denis (93)";
        $depts["94"] = "Val de Marne (94)";
        $depts["95"] = "Val d'Oise (95)";
        $depts["97"] = "DOM (97)";

        return isset($depts[$code]) ? $depts[$code] : '';
    }

    /**
     * Geo geopoints from a physical address
     *
     * @param string $rawAddress Address
     *
     * @return string Geopoints lat,lng
     *
     * @throws \Exception
     */
    public function getGeopointsFromAddress($rawAddress)
    {
        $cache = $this->cache;
        $key = $rawAddress;
        try {
            if (!$cache->exists($key)) {
                $geopoints = $this->getGeopoints($rawAddress);
                $cache->save($key, $geopoints);
            }
        } catch (\Exception $e) {
            $cache->save($key, 'error');
            throw new \Exception('Value ' . $rawAddress . ' could not be geocoded:' . $e->getMessage());
        }

        $geopoints = $cache->get($key);
        if ($geopoints === 'error') {
            return null;
        }

        return $geopoints;
    }

    /**
     * Geo geopoints from $value
     *
     * @param string $value Raw address
     *
     * @return string Geopoints lat,lng
     *
     * @throws \Exception
     */
    public function getGeopoints($value)
    {
        $locationServiceClient = new \Aws\LocationService\LocationServiceClient([
            'region' => getenv('AWS_REGION'),
            'version' => self::AWS_LOCATION_SERVICE_VERSION,
        ]);
        $response = $locationServiceClient->searchPlaceIndexForText([
            'IndexName' => getenv('AWS_LOCATION_INDEX_NAME'),
            'FilterCountries' => $this->countriesFilter,
            'Text' => $value,
        ]);
        $results = $response->get('Results');
        if (
            !empty($results)
            && isset($results[0]['Place']['Geometry']['Point'][1])
            && isset($results[0]['Place']['Geometry']['Point'][0])
        ) {
            return sprintf(
                '%s,%s',
                $results[0]['Place']['Geometry']['Point'][1],
                $results[0]['Place']['Geometry']['Point'][0]
            );
        }

        return 'error';
    }
}
