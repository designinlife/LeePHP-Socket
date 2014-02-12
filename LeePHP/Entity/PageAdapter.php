<?php
namespace LeePHP\Entity;

use LeePHP\Interfaces\IDbAdapter;
use LeePHP\ArgumentException;
use LeePHP\Base\Base;

/**
 * 数据分页处理对象。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class PageAdapter extends Base implements IDbAdapter {
    private $_currentPage  = 1;
    private $_totalPage    = 1;
    private $_recordCount  = 0;
    private $_pageSize     = 10;
    private $_startIndex   = 0;
    private $_endIndex     = 0;
    private $_countQuery   = NULL;
    private $_resultQuery  = NULL;
    private $_countParams  = NULL;
    private $_resultParams = NULL;
    private $_pageStyle    = 1;
    private $_linkPattern  = NULL;
    private $_linkNames    = NULL;
    private $_offsetName   = 'offset';

    /**
     * 构造函数。
     * 
     * @param \Application\Bootstrap $scope 指定 Bootstrap 上下文对象实例。
     */
    function __construct($scope) {
        parent::__construct($scope);

        $this->_linkNames = array('首页', '上页', '下页', '尾页');
    }

    /**
     * 执行 SQL 分页语句并返回结果集。
     * 
     * @return array
     */
    function execute() {
        if (!$this->_countQuery)
            throw new ArgumentException('未设置统计查询 SQL 语句。', -1);
        if (!$this->_resultQuery)
            throw new ArgumentException('未设置结果查询 SQL 语句。', -1);
        if ($this->_pageSize < 1)
            throw new ArgumentException('参数 $page_size 不能小于 1。', -1);
        if (false !== stripos($this->_resultQuery, ' LIMIT '))
            throw new ArgumentException(__CLASS__ . '::setResultQuery() 方法参数 SQL 中不允许包含 LIMIT 关键字。', -1);

        // 执行 SQL 统计查询 ...
        $this->_recordCount = ( int ) $this->ctx->db->scalar($this->_countQuery, $this->_countParams);

        if ($this->_currentPage < 1)
            $this->_currentPage = 1;

        $this->_totalPage = ceil($this->_recordCount / $this->_pageSize);

        // 防止当前页数溢出 ...
        if ($this->_currentPage > $this->_totalPage)
            $this->_currentPage = $this->_totalPage;

        $this->_startIndex = ($this->_currentPage - 1) * $this->_pageSize;
        $this->_endIndex   = $this->_startIndex + $this->_pageSize;

        // 执行结果查询 ...
        $d = $this->ctx->db->fetchAll($this->_resultQuery . ' LIMIT ' . $this->_startIndex . ',' . $this->_pageSize, $this->_resultParams);

        $dr = array();

        $dr['RecordCount'] = $this->_recordCount;
        $dr['CurrentPage'] = $this->_currentPage;
        $dr['PageCount']   = $this->_totalPage;
        $dr['PageSize']    = $this->_pageSize;
        $dr['Html']        = $this->_toHtmlPage();
        $dr['Results']     = $d;

        return $dr;
    }

    /**
     * 释放内存。
     */
    function dispose() {
        unset($this->_countParams, $this->_resultParams);

        $this->_countParams  = NULL;
        $this->_resultParams = NULL;
    }

    /**
     * 字符串序列化。
     * 
     * @return string
     */
    function __toString() {
        $d = array(
            'RecordCount' => $this->_recordCount,
            'CurrentPage' => $this->_currentPage,
            'PageSize'    => $this->_pageSize,
            'PageCount'   => $this->_totalPage,
        );

        return json_encode($d);
    }

    /**
     * 设置分页 GET 变量名称。
     * 
     * @param string $offsetName
     * @return \Application\Entity\PageAdapter
     */
    function setOffsetName($offsetName) {
        $this->_offsetName = $offsetName;
        return $this;
    }

    /**
     * 设置链接模式字符串。(注: 此参数最终使用 sprintf() 函数计算结果.)
     * 
     * @param string $linkPattern 指定模式字符串。(例如: /index.php?page=%d )
     * @return \Application\Entity\PageAdapter
     */
    function setLinkPattern($linkPattern) {
        $this->_linkPattern = $linkPattern;
        return $this;
    }

    /**
     * 设置分页 HTML 代码样式。
     * 
     * @param int $pageStyle 指定样式。(可选值: 1,[首页][上页][下页][尾页] | 2,Discuz X2 分页样式 | 3,Google 分页样式)
     * @return \Application\Entity\PageAdapter
     */
    function setPageStyle($pageStyle) {
        $this->_pageStyle = $pageStyle;
        return $this;
    }

    /**
     * 设置统计查询参数集合。
     * 
     * @param array $countParams
     * @return \Application\Entity\PageAdapter
     */
    function setCountParams($countParams) {
        $this->_countParams = $countParams;
        return $this;
    }

    /**
     * 设置结果查询参数集合。
     * 
     * @param array $resultParams
     * @return \Application\Entity\PageAdapter
     */
    function setResultParams($resultParams) {
        $this->_resultParams = $resultParams;
        return $this;
    }

    /**
     * 设置统计记录数的 SQL 语义字符串。
     * 
     * @param string $countQuery
     * @param array $dataParams
     * @return \Application\Entity\PageAdapter
     */
    function setCountQuery($countQuery, $dataParams = NULL) {
        $this->_countQuery  = $countQuery;
        $this->_countParams = $dataParams;
        return $this;
    }

    /**
     * 设置查询结果的 SQL 语义字符串。
     * 
     * @param string $resultQuery
     * @param array $dataParams
     * @return \Application\Entity\PageAdapter
     */
    function setResultQuery($resultQuery, $dataParams = NULL) {
        $this->_resultQuery  = $resultQuery;
        $this->_resultParams = $dataParams;
        return $this;
    }

    /**
     * 获取当前页码。
     * 
     * @return int
     */
    function getCurrentPage() {
        return $this->_currentPage;
    }

    /**
     * 设置当前页码。
     * 
     * @param int $currentPage
     * @return \Application\Entity\PageAdapter
     */
    function setCurrentPage($currentPage) {
        $this->_currentPage = $currentPage;
        return $this;
    }

    /**
     * 获取总页数。
     * 
     * @return int
     */
    function getTotalPage() {
        return $this->_totalPage;
    }

    /**
     * 获取总记录数。
     * 
     * @return int
     */
    function getRecordCount() {
        return $this->_recordCount;
    }

    /**
     * 获取每页显示 X 条记录。
     * 
     * @return int
     */
    function getPageSize() {
        return $this->_pageSize;
    }

    /**
     * 设置每页显示 X 条记录。
     * 
     * @param int $pageSize
     * @return \Application\Entity\PageAdapter
     */
    function setPageSize($pageSize) {
        $this->_pageSize = $pageSize;
        return $this;
    }

    /**
     * 获取当前页记录起始索引值。
     * 
     * @return int
     */
    function getStartIndex() {
        return $this->_startIndex;
    }

    /**
     * 获取当前页记录截止索引值。
     * 
     * @return int
     */
    function getEndIndex() {
        return $this->_endIndex;
    }

    /**
     * 设置链接名称。(默认值: [首页][上页][下页][尾页])
     * 
     * @param array $linkNames
     * @return \Application\Entity\PageAdapter
     */
    function setLinkNames($linkNames) {
        $this->_linkNames = $linkNames;
        return $this;
    }

    /**
     * 获取分页样式 HTML 代码。
     * 
     * @return string
     */
    private function _toHtmlPage() {
        $d = array();

        if (1 == $this->_pageStyle) {
            if ($this->_currentPage != 1) {
                $d[] = '<a href="' . $this->_buildLinkStr(1) . '" class="tf">' . $this->_linkNames[0] . '</a>';
                $d[] = '<a href="' . $this->_buildLinkStr($this->_currentPage - 1) . '" class="tp">' . $this->_linkNames[1] . '</a>';
            }
            if ($this->_currentPage < $this->_totalPage) {
                $d[] = '<a href="' . $this->_buildLinkStr($this->_currentPage + 1) . '" class="tf">' . $this->_linkNames[2] . '</a>';
                $d[] = '<a href="' . $this->_buildLinkStr($this->_totalPage) . '" class="tp">' . $this->_linkNames[3] . '</a>';
            }

            return implode('', $d);
        } elseif (2 == $this->_pageStyle) {
            return $this->_discuz($this->_recordCount, $this->_pageSize, $this->_currentPage, $this->_totalPage, 2, false, false);
        } elseif (3 == $this->_pageStyle) {
            return $this->_google($this->_currentPage, $this->_totalPage);
        }

        return '';
    }

    /**
     * Google 分页样式。
     * 
     * @param int $p
     * @param int $total
     * @return string
     */
    function _google($p, $total) {
        $prevs = $p - 10;
        if ($prevs <= 0) {
            $prevs = 1;
        }
        $prev = $prevs - 1;
        if ($prev <= 0) {
            $prev = 1;
        }
        $nexts = $p + 9;
        if ($nexts > $total) {
            $nexts = $total;
        }
        $next = $nexts + 1;
        if ($next > $total) {
            $next = $total;
        }

        $pagenavi = '<a href="' . $this->_buildLinkStr(1) . '">' . $this->_linkNames[0] . '</a> ';
        $pagenavi.= '<a href="' . $this->_buildLinkStr($prev) . '">' . $this->_linkNames[1] . '</a> ';
        for ($i = $prevs; $i <= $p - 1; $i++) {
            $pagenavi.= '<a href="' . $this->_buildLinkStr($i) . '">' . $i . '</a> ';
        }
        $pagenavi.= '<strong>' . $p . '</strong> ';
        for ($i = $p + 1; $i <= $nexts; $i++) {
            $pagenavi.= '<a href="' . $this->_buildLinkStr($i) . '">' . $i . '</a> ';
        }
        $pagenavi.= '<a href="' . $this->_buildLinkStr($next) . '">' . $this->_linkNames[2] . '</a> ';
        $pagenavi.= '<a href="' . $this->_buildLinkStr($total) . '">' . $this->_linkNames[3] . '</a> ';

        return $pagenavi;
    }

    /**
     * Discuz X 分页函数。
     * 
     * @param int $num
     * @param int $perpage
     * @param int $curpage
     * @param int $maxpages
     * @param int $page
     * @param boolean $autogoto
     * @param boolean $simple
     * @return string
     */
    function _discuz($num, $perpage, $curpage, $maxpages = 0, $page = 10, $autogoto = TRUE, $simple = FALSE) {
        $multipage = '';
        $realpages = 1;
        //判断总条数是否大于设置的每页要显示的条数    
        if ($num > $perpage) {
            //设置在$multipage中当前页数之前还要输出几个页数    
            $offset = 2;

            $realpages = @ceil($num / $perpage);
            //总共的页数（不知道$maxpages的意思），这里假设是15条    
            $pages     = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
            //如果总页数小于multipage中要输出的页数$page，则只输出到实际页数为止       
            if ($page > $pages) {
                $from = 1;
                $to   = $pages;
                //如果大于的话，就要输出$page个页数（我们假设的的15条就符合这个条件）    
            } else {
                $from = $curpage - $offset;
                $to   = $from + $page - 1;
                //假设curpage为4，目前为止，from为2，to为11    
                //下面假设curpage为1    

                if ($from < 1) {
                    $to   = $curpage + 1 - $from;
                    $from = 1;
                    //目前为止from为1，to为3    
                    if ($to - $from < $page) {
                        //因为这里的前提条件是总条数大于page，所以，如果$to-$from小于page的话显然达不到目的，应把$to设置为$page    
                        $to = $page;
                    }//目前为止 from为1 ，to为10    
                } elseif ($to > $pages) {//to是不可以大于总页数的    
                    $from = $pages - $page + 1;
                    $to   = $pages;
                }
            }

            $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $this->_buildLinkStr(1) . 'page=1" class="first">1 ...</a>' : '') . ($curpage > 1 && !$simple ? '<a href="' . $this->_buildLinkStr($curpage - 1) . '" class="prev">&lt;&lt;</a>' : '');
            for ($i = $from; $i <= $to; $i++) {
                $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' :
                    '<a href="' . $this->_buildLinkStr($i) . ($i == $pages && $autogoto ? '#' : '') . '">' . $i . '</a>';
            }

            $multipage .= ($curpage < $pages && !$simple ? '<a href="' . $this->_buildLinkStr($curpage + 1) . '" class="next">&gt;&gt;</a>' : '') .
                ($to < $pages ? '<a href="' . $this->_buildLinkStr($pages) . '" class="last">... ' . $realpages . '</a>' : '') .
                (!$simple && $pages > $page ? '<kbd><input type="text" name="custompage" size="3"  /></kbd>' : '');

            // $multipage = $multipage ? '<div class="pages">' . (!$simple ? '<em> ' . $num . ' </em>' : '') . $multipage . '</div>' : '';
        }

        return $multipage;
    }

    /**
     * 构建链接地址。
     *
     * @param integer $offset
     * @param integer $index
     * @return string
     */
    private function _buildLinkStr($index) {
        if ($this->_linkPattern != NULL) {
            return sprintf($this->_linkPattern, $index);
        } else {
            if (empty($_SERVER['QUERY_STRING'])) {
                return $_SERVER['SCRIPT_NAME'] . '?' . $this->_offsetName . '=' . $index;
            } else {
                if (preg_match('/(\?|&)' . $this->_offsetName . '=[\-0-9]*/i', $_SERVER['REQUEST_URI'])) {
                    return preg_replace('/(\?|&)' . $this->_offsetName . '=[\-0-9]*/is', '\\1' . $this->_offsetName . '=' . $index, $_SERVER['REQUEST_URI']);
                } else {
                    return $_SERVER['REQUEST_URI'] . '&' . $this->_offsetName . '=' . $index;
                }
            }
        }
    }
}
