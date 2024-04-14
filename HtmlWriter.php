<?php

declare(strict_types=1);

namespace TestPrj;

class HtmlWriter
{
    /* 
     * Формирует строку содержащую HTML для отображения списка ошибок
     * Принимает на вход: 
     *     array $errors - массив ошибок
     * Возвращает:
     *     string $errorsHTML - строка содержащая HTML для отображения списка ошибок
     */
    public static function getErrorsHTML(array $errors = []): string
    {
        $errorsHTML = '';

        if (count($errors)) {
            $errorsHTML .= '<div class="form__errors">*' . implode(', ', $errors) . '</div>';
        }
        
        return $errorsHTML;
    }

    /* 
     * Формирует строку содержащую HTML для отображения сообщения
     * Принимает на вход: 
     *     string $message - сообщение
     * Возвращает:
     *     string $messageHTML - строка содержащая HTML для отображения сообщения
     */
    public static function getMessageHTML(string $message): string
    {
        $messageHTML = '';

        if (!empty($message)) {
            $messageHTML .= '<div class="form__message">*' . $message . '</div>';
        }

        return $messageHTML;
    }

    /* 
     * Формирует строку содержащую HTML для отображения списка IP адресов с информацией
     * о их местоположении
     * Принимает на вход: 
     *     string $message - сообщение
     * Возвращает:
     *     string $ipListHTML - строка содержащая HTML для отображения списка IP адресов с информацией
     *     о их местоположении
     */
    public static function getIPListHTML(array $ipData): string
    {
        $ipListHTML = '';

        if (count($ipData)) {
            foreach ($ipData as $i => $ipInfo) {
                $ipListHTML .= '<li class="list-group-item">';

                $col_str = IpGeoConst::DB_COLS_STR[IpGeoConst::DB_COLS[0]];
                $val = ($ipInfo[0]) ? $ipInfo[0] : IpGeoConst::DEFAULT_STR;

                $ipListHTML .= '<div class="ip_list__item_controls"><button class="control_btn btn btn-primary ip_btn_collapse" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#collapse' . $i . '">' . 
                    $col_str . ': ' . $val . '</button>';

                $ipListHTML .= '<a href="?action=delete&ip='. $val .'" class="control_link link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Удалить</a></div>';

                $ipListHTML .= '<div class="collapse ip_info_collapse" id="collapse' . $i . '">
                    <div class="card card-body">';
                for ($j = 1; $j < count($ipInfo); $j++) {
                    $col_str = IpGeoConst::DB_COLS_STR[IpGeoConst::DB_COLS[$j]];
                    $val = ($ipInfo[$j]) ? $ipInfo[$j] : IpGeoConst::DEFAULT_STR;
                    $ipListHTML .= '<p><span class="col_name">' . $col_str .
                        '</span>: <span class="col_val">' . $val . '</span></p>';
                }
                $ipListHTML .= '</div></div>';
                $ipListHTML .= '</li>';
            }
        } else {
            $ipListHTML = '<h3>Список пуст</h2>';
        }

        return $ipListHTML;
    }

    /* 
     * Формирует строку содержащую HTML для отображения элементов отвечающих 
     * за постраничный вывод
     * Принимает на вход: 
     *     int $page - номер текушей страницы
     *     int $totalPages - общее кол-во страниц
     * Возвращает:
     *     string $paginationHTML - строка содержащая HTML для отображения 
     *     элементов отвечающих за постраничный вывод
     */
    public static function getPaginationHTML(int $page, int $totalPages): string
    {
        $paginationHTML = '';

        if ($totalPages) {
            $paginationHTML = '<nav><ul class="pagination">';
                        
            if ($page >= 2) {
                $paginationHTML .= '<li class="page-item">
                    <a class="page-link" href="?page='. ($page - 1) .'">
                    <span>&laquo;</span></a></li>';
            }

            $paginationHTML .= '<li class="page-item"><a class="page-link" href="#">
                ' . $page . '/' . $totalPages . '</a></li>';
                        
            if ($page < $totalPages) {
                $paginationHTML .= '<li class="page-item">
                    <a class="page-link" href="?page='. ($page + 1) .'">
                    <span>&raquo;</span></a></li>';
            }

            $paginationHTML .= '</ul></nav>';
        }

        return $paginationHTML;
    }
}
