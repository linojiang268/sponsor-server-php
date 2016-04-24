<?php
namespace Sponsor\Utils;

final class PaginationUtil
{
    public static function computePages($count, $size)
    {
        return intval(ceil($count / $size));
    }
    /**
     * sane give page - make page be within the range of [0, max_available_pag]
     * @param $total
     * @param $page
     * @param $size
     * @return int
     */
    public static function sanePage($total, $page, $size)
    {
        if ($page < 0) {
            return 0;
        }

        $pages = self::computePages($total, $size);
        return $page <= $pages ? $page : $pages;
    }
}
