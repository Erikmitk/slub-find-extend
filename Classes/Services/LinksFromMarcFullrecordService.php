<?php

namespace Slub\SlubFindExtend\Services;

use Solarium\QueryType\Select\Result\Document;
use Slub\SlubFindExtend\Services\MarcRefrenceResolverService;

/**
 * Class StatusService
 * @package Slub\SlubFindExtend\Services
 */
class LinksFromMarcFullrecordService
{

    /**
     * @var \Slub\SlubFindExtend\Services\MarcRefrenceResolverService
     * @inject
     */
    protected $marcRefrenceResolverService;

    /**
     * Returns the links from the MARC fullrecord
     *
     * @param object $fullrecord
     * @param array $isil
     * @return array
     */
    public function getLinks($fullrecord, $isil = NULL)
    {
        $defaultPrefix = 'http://wwwdb.dbod.de/login?url=';
        $noPrefixHosts = ['wwwdb.dbod.de', 'dx.doi.org', 'nbn-resolving.de', 'digital.slub-dresden.de'];

        $resourceLinks = [];
        $relatedLinks = [];
        $isilLinks = [];

        $reference = $this->marcRefrenceResolverService->resolveReference('856', $fullrecord);

        for ($i = 0; $i < count($reference->cache["856"]); $i++) {

            $prefix = $defaultPrefix;
            $note = '';
            $material = '';
            $ind1 = $reference->cache["856[" . $i . "]"]->getIndicator(1);
            $ind2 = $reference->cache["856[" . $i . "]"]->getIndicator(2);

            if($reference->cache["856[" . $i . "]"]->getSubfield('u')) {
                $uri = trim($reference->cache["856[" . $i . "]"]->getSubfield('u')->getData());

                if (substr($uri, 0, 4) === "urn:") {
                    $uri = 'http://nbn-resolving.de/' . $uri;
                }
                $uri = str_replace('https://wwwdb.dbod.de/login?url=', '', $uri);

                $uriParsed = parse_url($uri);

                if(in_array($uriParsed['host'], $noPrefixHosts)) { $prefix =  ''; }

                if ($reference->cache["856[" . $i . "]"]->getSubfield('z')) {
                    $note = $reference->cache["856[" . $i . "]"]->getSubfield('z')->getData();
                }
                if ($reference->cache["856[" . $i . "]"]->getSubfield('3')) {
                    $material = $reference->cache["856[" . $i . "]"]->getSubfield('3')->getData();
                }

                if ($reference->cache["856[" . $i . "]"]->getSubfield('9') && in_array($reference->cache["856[" . $i . "]"]->getSubfield('9')->getData(), $isil)) {
                    $isilLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => $prefix];
                } elseif (($ind1 === '4') && ($ind2 === '2')) {
                    $relatedLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => ''];
                } elseif (($ind1 === '4') && ($ind2 === '0')) {
                    $resourceLinks[] = ["uri" => $uri, "note" => $note, "material" => $material, "prefix" => $prefix];
                }
            }

        }

        return [
            'isil' => $isilLinks,
            'resource' => $resourceLinks,
            'related' => $relatedLinks
        ];

    }

    /**
     * To check the if uri exits
     */
    private function in_array_field($needle, $needle_field, $haystack)
    {
        foreach ($haystack as $item)
            if (isset($item[$needle_field]) && $item[$needle_field] == $needle)
                return true;
        return false;
    }

}
