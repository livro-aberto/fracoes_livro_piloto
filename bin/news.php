<?php

require_once('/var/www/livro/inc/changelog.php');
//require_once('../../../inc/search.php');
//require_once('../../../inc/pageutils.php');
require_once('/var/www/livro/inc/init.php');
require_once('/var/www/livro/inc/common.php');
require_once('/var/www/livro/inc/io.php');
require_once('/var/www/livro/inc/search.php');
require_once('/var/www/livro/inc/pageutils.php');
require_once('/var/www/livro/inc/parserutils.php');

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

class helper_plugin_discussion{

    function th() {
        return '';//$this->getLang('discussion');
    }
    /**
     * Returns the link to the discussion section of a page
     *
     * @param string $id
     * @param null|int $num
     * @return string
     */
    function td($id, $num = null) {
        $section = '#discussion__section';
        if (!isset($num)) {
            $cfile = metaFN($id, '.comments');
            $comments = unserialize(io_readFile($cfile, false));
            $num = $comments['number'];
            if ((!$comments['status']) || (($comments['status'] == 2) && (!$num))) return '';
        }
        if ($num == 0) {
            $comment = '0&nbsp;';
        } elseif ($num == 1) {
            $comment = '1&nbsp;';
        } else {
            $comment = $num;
        }
        return '<a href="'.wl($id).$section.'" class="wikilink1" title="'.$id.$section.'">'.
            $comment.'</a>';
    }

    function getThreads($ns, $num = null, $skipEmpty = false) {
        global $conf;
        $dir = '/var/www/livro/data/gitrepo/pages/';
        // returns the list of pages in the given namespace and it's subspaces
        $items = array();
        search($items, $dir, 'search_allpages', array());
        // add pages with comments to result
        $result = array();
        foreach ($items as $item) {
            $id   = ($ns ? $ns.':' : '').$item['id'];
            // some checks
            $perm = auth_quickaclcheck($id);
            if ($perm < AUTH_READ) continue;    // skip if no permission
            $file = metaFN($id, '.comments');
            if (!@file_exists($file)) continue; // skip if no comments file
            $data = unserialize(io_readFile($file, false));
            $status = $data['status'];
            $number = $data['number'];
            if (!$status || (($status == 2) && (!$number))) continue; // skip if comments are off or closed without comments
            if($skipEmpty == 'y' && $number == 0) continue; // skip if discussion is empty and flag is set
            $date = filemtime($file);
            $meta = p_get_metadata($id);
            $result[$date.'_'.$id] = array(
                    'id'       => $id,
                    'file'     => $file,
                    'title'    => $meta['title'],
                    'date'     => $date,
                    'user'     => $meta['creator'],
                    'desc'     => $meta['description']['abstract'],
                    'num'      => $number,
                    'comments' => $this->td($id, $number),
                    'status'   => $status,
                    'perm'     => $perm,
                    'exists'   => true,
                    'anchor'   => 'discussion__section',
                    );
        }
        // finally sort by time of last comment
        krsort($result);
        if (is_numeric($num)) $result = array_slice($result, 0, $num);
        return $result;
    }


    /**
     * Returns an array of recently added comments to a given page or namespace
     * Note: also used for content by Feed Plugin
     *
     * @param string $ns
     * @param int|null $num
     * @return array
     */
    function getComments($ns = 1, $num = NULL) {
        $result = array();
        $count  = 0;
        if (!@file_exists('/var/www/livro/data/meta/_comments.changes')) {print('ahhh');return $result;}
        // read all recent changes. (kept short)
        $lines = file('/var/www/livro/data/meta/_comments.changes');
        $seen = array(); //caches seen pages in order to skip them
        // handle lines
        $line_num = count($lines);
        for ($i = ($line_num - 1); $i >= 0; $i--) {
            $rec = $this->_handleRecentComment($lines[$i], $ns, $seen);
            if ($rec !== false) {
                //$randhead = 'code: '.$rec['date'].' -';
                $randhead = '';
                print($randhead.' usuario: '.$rec['user'].'
');
                print($randhead.' pagina: '.$rec['id'].'
');
                print($randhead.' disse: '.$rec['desc']);

                //if (--$first >= 0) continue; // skip first entries
                $result[$rec['date']] = $rec;
                $count++;
                // break when we have enough entries
                //if ($count >= $num) break;
            }
        }
        // finally sort by time of last comment
        krsort($result);
        return $result;
    }
    /* ---------- Changelog function adapted for the Discussion Plugin ---------- */
    /**
     * Internal function used by $this->getComments()
     *
     * don't call directly
     *
     * @see getRecentComments()
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Esther Brunner <wikidesign@gmail.com>
     *
     * @param string $line
     * @param string $ns
     * @param array  $seen
     * @return array|bool
     */
    function _handleRecentComment($line, $ns, &$seen) {
        if (empty($line)) return false;  //skip empty lines
        // split the line into parts
        $recent = parseChangelogLine($line);
        if ($recent === false) return false;
        $cid     = $recent['extra'];
        $fullcid = $recent['id'].'#'.$recent['extra'];
        // skip seen ones
        if (isset($seen[$fullcid])) return false;
        // skip 'show comment' log entries
        if ($recent['type'] === 'sc') return false;
        // remember in seen to skip additional sights
        $seen[$fullcid] = 1;
        if ($recent['type'] === 'hc') return false;
        // filter namespace or id
        if (($ns) && (strpos($recent['id'].':', $ns.':') !== 0)) return false;
        // check existance
        $recent['file'] = wikiFN($recent['id']);
        $recent['exists'] = @file_exists($recent['file']);
        if (!$recent['exists']) return false;
        if ($recent['type'] === 'dc') return false;
        // get discussion meta file name
        $data = unserialize(io_readFile(metaFN($recent['id'], '.comments'), false));
        // check if discussion is turned off
        if ($data['status'] === 0) return false;
        $parent_id = $cid;
        // Check for the comment and all parents if they exist and are visible.
        do  {
            $tcid = $parent_id;
            // check if the comment still exists
            if (!isset($data['comments'][$tcid])) return false;
            // check if the comment is visible
            if ($data['comments'][$tcid]['show'] != 1) return false;
            $parent_id = $data['comments'][$tcid]['parent'];
        } while ($parent_id && $parent_id != $tcid);
        // okay, then add some additional info
        if (is_array($data['comments'][$cid]['user'])) {
            $recent['name'] = $data['comments'][$cid]['user']['name'];
        } else {
            $recent['name'] = $data['comments'][$cid]['name'];
        }
        $recent['desc'] = strip_tags($data['comments'][$cid]['xhtml']);
        $recent['anchor'] = 'comment_'.$cid;
        return $recent;
    }

}

$foo = new helper_plugin_discussion;

$recent = $foo->getComments('',10);

//print_r($recent);

//$recent = $foo->getThreads('');

//print_r($recent);
