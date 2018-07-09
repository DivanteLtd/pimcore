<?php

namespace Pimcore\Bundle\AdminBundle\Controller\Admin;

use Pimcore\Bundle\AdminBundle\Helper\QueryParams;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NotificationController
 *
 * @package Pimcore\Bundle\AdminBundle\Controller\Admin
 */
class NotificationController
{

    /**
     * @param Request $request
     *
     * @return array
     */
    private function parseOptions(Request $request): array
    {
        $sort = ['createdAt' => -1];

        $settings = QueryParams::extractSortingSettings(
            array_merge(
                $request->request->all(),
                $request->query->all()
            )
        );

        if (isset($settings['orderKey'])) {
            $key   = $settings['orderKey'];
            $order = $settings['order'];
            $sort  = [$key => $order];
        }

        return [
            'sort'  => $sort,
            'skip'  => (int) $request->get('start', 0),
            'limit' => (int) $request->get('limit', 50),
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function parseFilter(Request $request): array
    {
        $params  = json_decode($request->get('filter'));

        $filters = [];

        foreach ($params as $key => $param) {
            if ($param['operator'] === 'gt') {
                $filters[$key]['greater'] = $param['value'];
                $filters[$key]['field']   = $param['property'];
                $filters[$key]['type']    = $param['type'];
            }

            if ($param['operator'] === 'lt') {
                $filters[$key]['lower'] = $param['value'];
                $filters[$key]['field'] = $param['property'];
                $filters[$key]['type']  = $param['type'];
            }

            if ($param['operator'] === 'like') {
                $filters[$key]['like']  = $param['value'];
                $filters[$key]['field'] = $param['property'];
                $filters[$key]['type']  = $param['type'];
            }

            if ($param['operator'] === 'in') {
                $filters[$key]['select'] = $param['value'];
                $filters[$key]['field']  = $param['property'];
                $filters[$key]['type']   = $param['type'];
            }

            if ($param['operator'] === 'eq') {
                $filters[$key]['equals'] = $param['value'];
                $filters[$key]['field']  = $param['property'];
                $filters[$key]['type']   = $param['type'];
            }

            if ($param['operator'] === '=') {
                $filters[$key]['bool']   = $param['value'];
                $filters[$key]['field']  = $param['property'];
                $filters[$key]['type']   = $param['type'];
            }
        }

        return $filters;
    }
}
