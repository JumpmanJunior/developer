<?php

/**
 * File: class_masterserver.php.
 * Author: Ulrich Block
 * Date: 16.09.12
 * Time: 11:27
 * Contact: <ulrich.block@easy-wi.com>
 *
 * This file is part of Easy-WI.
 *
 * Easy-WI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Easy-WI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy-WI.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von Easy-WI.
 *
 * Easy-WI ist Freie Software: Sie koennen es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder spaeteren
 * veroeffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * Easy-WI wird in der Hoffnung, dass es nuetzlich sein wird, aber
 * OHNE JEDE GEWAEHELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewaehrleistung der MARKTFAEHIGKEIT oder EIGNUNG FUER EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License fuer weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 */
class masterServer {

    // General Data
    private $imageserver, $resellerID, $webhost, $rootOK;
    
    // root data
    private $rootID, $steamAccount, $steamPassword;
    public $sship, $sshport, $sshuser, $sshpass, $publickey, $keyname;
    
    // master gamedata
    private $updateIDs = array(), $syncList = array(), $steamCmdTotal = array('sync' => array(), 'nosync' => array()), $steamCmdOutdated = array('sync' => array(),'nosync' => array()), $hldsTotal = array('sync' => array(),'nosync' => array()), $hldsOutdated = array('sync' => array(),'nosync' => array()), $mcTotal = array('sync' => array(),'nosync' => array()), $mcOutdated = array('sync' => array(),'nosync' => array()), $noSteam = array('sync' => array(),'nosync' => array()), $maps = array(), $addons = array(), $aeskey;
    
    //ssh command
    public $sshcmd;

    function __construct($rootID, $aeskey) {

        // fetch global PDO object
        global $sql;

        $this->aeskey = $aeskey;

        // get the current webhost
        $query = $sql->prepare("SELECT `paneldomain` FROM `settings` WHERE `resellerid`='0' LIMIT 1");
        $query->execute();
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->webhost = $row['paneldomain'];
        }

        // store the rootserverID
        $this->rootID = $rootID;

        // fetch rootserverdata
        $query = $sql->prepare("SELECT *,AES_DECRYPT(`port`,:aeskey) AS `dport`,AES_DECRYPT(`user`,:aeskey) AS `duser`,AES_DECRYPT(`pass`,:aeskey) AS `dpass`,AES_DECRYPT(`steamAccount`,:aeskey) AS `steamAcc`,AES_DECRYPT(`steamPassword`,:aeskey) AS `steamPwd` FROM `rserverdata` WHERE `id`=:id LIMIT 1");
        $query->execute(array(':aeskey' => $aeskey,':id' => $rootID));
        foreach ($query->fetchAll() as $row) {
            $active = $row['active'];
            $this->sship = $row['ip'];
            $this->sshport = $row['dport'];
            $this->sshuser = $row['duser'];
            $this->sshpass = $row['dpass'];
            $this->publickey = $row['publickey'];
            $this->keyname = $row['keyname'];
            $this->steamAccount = $row['steamAcc'];
            $this->steamPassword = $row['steamPwd'];
            $this->resellerID = $row['resellerid'];

            // Get the imageserver if possible and use Easy-WI server as fallback
            $mainip=explode('.', $this->sship);
            $mainsubnet = $mainip[0] . '.' . $mainip[1] . '.' . $mainip[2];
            $query = $sql->prepare("SELECT AES_DECRYPT(`imageserver`,?) AS `decryptedimageserver` FROM `settings`  WHERE `resellerid`=? LIMIT 1");
            $query->execute(array($aeskey, $this->resellerID));
            $splitImageservers=preg_split('/\r\n/', $query->fetchColumn(), -1, PREG_SPLIT_NO_EMPTY);
            $imageservers = array();
            foreach ($splitImageservers as $server) {
                $split2 = array();
                
                if (isurl($server)) {
                    $imageservers[] = $server;
                    $split1 = preg_split('/\//', $server, -1, PREG_SPLIT_NO_EMPTY);
                    $split2 = (isset($split1[1])) ? preg_split('/\@/', $split1[1], -1, PREG_SPLIT_NO_EMPTY) : preg_split('/\@/', $split1[0], -1, PREG_SPLIT_NO_EMPTY);
                    
                } else if (isRsync($server)) {
                    $imageservers[] = $server;
                    $split1 = preg_split('/\//', $server, -1, PREG_SPLIT_NO_EMPTY);
                    $split2 = (isset($split1[1])) ? preg_split('/\:/', $split1[1], -1, PREG_SPLIT_NO_EMPTY) : preg_split('/\:/', $split1[0], -1, PREG_SPLIT_NO_EMPTY);
                }
                
                foreach ($split2 as $splitip) {
                    
                    if ($splitip == $this->sship) {
                        $noSync = true;
                        
                    } else if (isip($splitip,'all')) {
                        $ipparts=explode('.', $splitip);
                        $subnet = $ipparts[0] . '.' . $ipparts[1] . '.' . $ipparts[2];
                        
                        if ($mainsubnet == $subnet) {
                            $imageserver = $server;
                        }
                    }
                }
            }
            
            if (!isset($imageserver) and count($imageservers) > 0) {
                $imageserver_count = count($imageservers) - 1;
                $arrayentry = rand(0, $imageserver_count);
                $imageserver = $imageservers[$arrayentry];
            }
            
            if (!isset($imageserver)) {
                $imageserver = 'easywi';
            }
            
            if (isset($noSync) or $row['updates'] == 2) {
                $imageserver = 'none';
            }
            
            $this->imageserver = $imageserver;
        }

        // In case the rootserver could be found and it is active return true
        if (isset($active) and $active == 'Y') {
            $this->rootOK = true;
        } else {
            $this->rootOK = false;
        }
    }

    // collect data regarding installed games
    public function collectData ($all = true, $force = false) {
        
        if ($this->rootOK != true) {
            return null;
        }

        // fetch global PDO object
        global $sql;

        if ($force == true) {
            $extraSQL = '';
        } else {
            $extraSQL = 'AND t.`updates`!=3 AND s.`updates`!=3';
        }

        // if an ID is given collect only data for this ID, else collect all game data for this rootserver
        if ($all === true) {
            
            $query = $sql->prepare("SELECT t.`id` AS `servertype_id`,t.`shorten`,t.`qstat`,t.`steamgame`,t.`appID`,t.`steamVersion`,t.`updates`,t.`downloadPath`,t.`gamebinary`,r.`localVersion`,s.`updates` AS `supdates` FROM `rservermasterg` r INNER JOIN `servertypes` t ON r.`servertypeid`=t.`id` INNER JOIN `rserverdata` s ON r.`serverid`=s.`id` WHERE r.`serverid`=? ${extraSQL}");
            $query->execute(array($this->rootID));
            
        } else {
            $query = $sql->prepare("SELECT t.`id` AS `servertype_id`,t.`shorten`,t.`qstat`,t.`steamgame`,t.`appID`,t.`steamVersion`,t.`updates`,t.`downloadPath`,t.`gamebinary`,r.`localVersion`,s.`updates` AS `supdates` FROM `rservermasterg` r INNER JOIN `servertypes` t ON r.`servertypeid`=t.`id` INNER JOIN `rserverdata` s ON r.`serverid`=s.`id` WHERE r.`serverid`=? AND r.`servertypeid`=? ${extraSQL} LIMIT 1");
            $query->execute(array($this->rootID, $all));
        }
        
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
            // 3 = no Update; 1 = Vendor + Sync; 2 = Vendor; 4 = Sync

            // Defined as Sync Only
            if (($row['supdates'] == 4 and $row['updates'] == 1) or (($row['supdates'] == 1 or $row['supdates'] == 4) and $row['updates'] == 4)) {
                $this->syncList[] = $row['shorten'];

                // Games defined to sync with 2 or 1
            } else if (($row['supdates'] == 1 or $row['supdates'] == 2) and ($row['updates'] == 1 or $row['updates'] == 2)) {

                // as rootserver and templates settings can be different, go with the least root settings
                $updateType = 0;
                
                if ($row['supdates'] == 1 and $row['updates'] == 1) {
                    $updateType = 1;
                    
                } else if ($row['supdates'] == 1 and $row['updates'] == 2) {
                    $updateType = 2;
                    
                } else if ($row['supdates'] == 2) {
                    $updateType = 2;
                }

                // steamCmd installations
                if ($row['steamgame'] == 'S') {
                    
                    $lookUpAppID=($row['appID'] == 90) ? $row['appID'] . '-' . $row['shorten'] : $row['appID'];
                    
                    if ($row['localVersion'] == null or ($row['localVersion'] != null and $row['localVersion'] < $row['steamVersion'])) {
                        if ($updateType == 1) {
                            $this->steamCmdOutdated['sync'][$lookUpAppID] = $row['shorten'];
                            
                        } else if ($updateType == 2) {
                            $this->steamCmdOutdated['nosync'][$lookUpAppID] = $row['shorten'];
                        }
                        
                    } else if ($updateType == 1) {
                        $this->syncList[] = $row['shorten'];
                    }
                    
                    if ($updateType == 1) {
                        $this->steamCmdTotal['sync'][$lookUpAppID] = $row['shorten'];
                        
                    } else if ($updateType == 2) {
                        $this->steamCmdTotal['nosync'][$lookUpAppID] = $row['shorten'];
                    }

                    // hlds installations
                } else if ($row['steamgame'] == 'Y') {
                    
                    if ($row['localVersion'] == null or ($row['localVersion'] != null and $row['localVersion'] < $row['steamVersion'])) {
                        if ($updateType == 1) {
                            $this->hldsOutdated['sync'][] = $row['shorten'];
                            
                        } else if ($updateType == 2) {
                            $this->hldsOutdated['nosync'][] = $row['shorten'];
                        }

                    } else if ($updateType == 1) {
                        $this->syncList[] = $row['shorten'];
                    }

                    if ($updateType == 1) {
                        $this->hldsTotal['sync'][] = $row['shorten'];
                        
                    } else if ($updateType == 2) {
                        $this->hldsTotal['nosync'][] = $row['shorten'];
                    }


                // Minecraft and Craftbukkit autoupdater
                // https://github.com/easy-wi/developer/issues/90 https://github.com/easy-wi/developer/issues/91
                } else if ($row['steamgame'] == 'N' and ($row['shorten'] == 'mc' or $row['shorten'] == 'bukkit')) {

                    
                    if ($row['localVersion'] == null or ($row['localVersion'] != null and $row['localVersion'] != $row['steamVersion'])) {
                        if ($updateType == 1) {
                            $this->mcOutdated['sync'][] = array('shorten' => $row['shorten'], 'url' => $row['downloadPath'], 'gamebinary' => $row['gamebinary']);

                        } else if ($updateType == 2) {
                            $this->mcOutdated['nosync'][] = array('shorten' => $row['shorten'], 'url' => $row['downloadPath'], 'gamebinary' => $row['gamebinary']);
                        }

                    } else if ($updateType == 1) {
                        $this->syncList[] = $row['shorten'];
                    }

                    if ($updateType == 1) {
                        $this->mcTotal['sync'][] = array('shorten' => $row['shorten'], 'url' => $row['downloadPath'], 'gamebinary' => $row['gamebinary']);

                    } else if ($updateType == 2) {
                        $this->mcTotal['nosync'][] = array('shorten' => $row['shorten'], 'url' => $row['downloadPath'], 'gamebinary' => $row['gamebinary']);
                    }
                    
                // the rest
                } else if ($row['steamgame'] == 'N') {
                    
                    if ($row['updates'] == 1) {
                        $this->noSteam['sync'][] = $row['shorten'];
                        
                    } else if ($updateType == 2) {
                        $this->noSteam['nosync'][] = $row['shorten'];
                    }
                }
            }

            if (($row['supdates'] == 1 or $row['supdates'] == 4) and ($row['updates'] == 1 or $row['updates'] == 4)) {
                // collect maps
                $query2 = $sql->prepare("SELECT DISTINCT(t.`addon`) FROM `addons_allowed` AS a INNER JOIN `addons` t ON a.`addon_id`=t.`id` WHERE t.`type`='map' AND a.`servertype_id`=? AND a.`reseller_id`=?");
                $query2->execute(array($row['servertype_id'], $this->resellerID));
                foreach ($query2->fetchAll(PDO::FETCH_ASSOC) as $row2) {
                    $this->maps[] = $row2['addon'];
                }

                // collect addons
                $query2 = $sql->prepare("SELECT DISTINCT(t.`addon`) FROM `addons_allowed` AS a INNER JOIN `addons` t ON a.`addon_id`=t.`id` WHERE t.`type`='tool' AND a.`servertype_id`=? AND a.`reseller_id`=?");
                $query2->execute(array($row['servertype_id'], $this->resellerID));
                foreach ($query2->fetchAll(PDO::FETCH_ASSOC) as $row2) {
                    $this->addons[] = $row2['addon'];
                }
            }
        }
    }

    // return command only for outdated servers
    public function returnCmds ($install = 'update', $update = true) {

        global $sql;

        if ($this->rootOK != true) {
            $this->sshcmd = null;
        }
        
        // Update if needed
        if ($update === true) {
            $steamCmdCount = count($this->steamCmdOutdated['sync']) + count($this->steamCmdOutdated['nosync']);
            $hldsCount = count($this->hldsOutdated['sync']) + count($this->hldsOutdated['nosync']);
            $mcCount = count($this->mcOutdated['sync']) + count($this->mcOutdated['nosync']);
            $steam = 'steamCmdOutdated';
            $hlds = 'hldsOutdated';
            $mc = 'mcOutdated';

        // Update in any case
        } else {
            $steamCmdCount = count($this->steamCmdTotal['sync']) + count($this->steamCmdTotal['nosync']);
            $hldsCount = count($this->hldsTotal['sync']) + count($this->hldsTotal['nosync']);
            $mcCount = count($this->mcTotal['sync']) + count($this->mcTotal['nosync']);
            $steam = 'steamCmdTotal';
            $hlds = 'hldsTotal';
            $mc = 'mcTotal';
            
            foreach (array_unique(array_merge($this->steamCmdTotal['sync'], $this->steamCmdTotal['nosync'], $this->hldsTotal['sync'], $this->hldsTotal['nosync'], $this->noSteam['sync'], $this->noSteam['nosync'])) as $shorten) {
                if (in_array($shorten, $this->syncList)) {
                    unset($this->syncList[array_search($shorten, $this->syncList)]);
                }
            }
        }
        
        $syncCount = count($this->syncList);
        $noSteamCount = count($this->noSteam['sync']) + count($this->noSteam['nosync']);
        $addonCount = count($this->maps) + count($this->addons);

        $query = $sql->prepare("SELECT r.`id` FROM `rservermasterg` r INNER JOIN `servertypes` t ON r.`servertypeid`=t.`id` WHERE r.`serverid`=? AND t.`shorten`=? LIMIT 1");

        // Nothing to update
        if ($syncCount == 0 and $noSteamCount == 0 and $steamCmdCount == 0 and $hldsCount == 0 and $mcCount == 0) {
            $this->sshcmd = null;
            
        } else {

            $tempCmd = array();

            // Sync games
            if ($syncCount>0 and $this->imageserver!='none') {
                foreach ($this->syncList as $k) {
                    $query->execute(array($this->rootID, $k));
                    $this->updateIDs[] = $query->fetchColumn();
                }

                $tempCmd[] = './control.sh syncserver '.$this->imageserver . ' "' . implode(' ', $this->syncList) . '" ' . $this->webhost;
            }

            // No Steam games
            if ($noSteamCount>0) {
                foreach (array_unique(array_merge($this->noSteam['sync'], $this->noSteam['nosync'])) as $k) {
                    $query->execute(array($this->rootID, $k));
                    $this->updateIDs[] = $query->fetchColumn();
                }
                if ($this->imageserver ==  'none') {
                    $tempCmd[] = './control.sh noSteamCmd '.$install . ' "' . implode(' ', array_unique(array_merge($this->noSteam['sync'], $this->noSteam['nosync']))) . '" ' . $this->webhost . ' ' . $this->imageserver;
                
                } else if (count($this->noSteam['sync']) > 0 and count($this->noSteam['nosync']) > 0) {
                    $tempCmd[] = './control.sh noSteamCmd '.$install . ' "' . implode(' ', $this->noSteam['sync']) . '" ' . $this->webhost . ' ' . $this->imageserver;
                    $tempCmd[] = './control.sh noSteamCmd '.$install . ' "' . implode(' ', $this->noSteam['nosync']) . '" ' . $this->webhost.' none';
                
                } else if (count($this->noSteam['sync']) > 0 and count($this->noSteam['nosync']) == 0) {
                    $tempCmd[] = './control.sh noSteamCmd '.$install . ' "' . implode(' ', $this->noSteam['sync']) . '" ' . $this->webhost . ' ' . $this->imageserver;
                
                } else if (count($this->noSteam['sync']) == 0 and count($this->noSteam['nosync']) > 0) {
                    $tempCmd[] = './control.sh noSteamCmd '.$install . ' "' . implode(' ', $this->noSteam['nosync']) . '" ' . $this->webhost.' none';
                }
            }

            // Minecraft Updates
            if ($mcCount>0) {

                $goFor = $this->$mc;

                $setToUpdate = array();
                $gameCommandListSync = array();
                $gameCommandListNoSync = array();

                foreach ($goFor['sync'] as $k) {
                    $setToUpdate[] = $k['shorten'];
                    $gameCommandListSync[] = $k['shorten'] . ';' . $k['gamebinary'] . ';' . $k['url'];
                }

                foreach ($goFor['nosync'] as $k) {
                    $setToUpdate[] = $k['shorten'];
                    $gameCommandListNoSync[] = $k['shorten'] . ';' . $k['gamebinary'] . ';' . $k['url'];
                }

                foreach (array_unique($setToUpdate) as $k) {
                    $query->execute(array($this->rootID, $k));
                    $this->updateIDs[] = $query->fetchColumn();
                }

                if ($this->imageserver ==  'none') {
                    $tempCmd[] = './control.sh mcUpdate '.$install . ' "' . implode(' ', array_unique(array_merge($gameCommandListSync, $gameCommandListNoSync))) . '" ' . $this->webhost . ' ' . $this->imageserver;

                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh mcUpdate '.$install . ' "' . implode(' ', $gameCommandListSync) . '" ' . $this->webhost . ' ' . $this->imageserver;
                    $tempCmd[] = './control.sh mcUpdate '.$install . ' "' . implode(' ', $gameCommandListNoSync) . '" ' . $this->webhost.' none';

                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) == 0) {
                    $tempCmd[] = './control.sh mcUpdate '.$install . ' "' . implode(' ', $gameCommandListSync) . '" ' . $this->webhost . ' ' . $this->imageserver;

                } else if (count($goFor['sync']) == 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh mcUpdate '.$install . ' "' . implode(' ', $gameCommandListNoSync) . '" ' . $this->webhost.' none';
                }
            }

            // steamCmd updates
            if ($steamCmdCount>0) {
                
                $goFor = $this->$steam;
                
                foreach (array_unique(array_merge($goFor['sync'], $goFor['nosync'])) as $k) {
                    $query->execute(array($this->rootID, $k));
                    $this->updateIDs[] = $query->fetchColumn();
                }
                
                if ($this->imageserver ==  'none') {
                    $combined = array();
                    
                    foreach ($goFor['sync'] as $k => $v) {
                        $combined[$k] = $v;
                    }
                    
                    foreach ($goFor['nosync'] as $k => $v) {
                        $combined[$k] = $v;
                    }

                    $tempCmd[] = './control.sh steamCmd '.$install . ' "' . trim($this->makeSteamCmd($combined)) . '" ' . $this->webhost . ' ' . $this->imageserver . ' ' . $this->steamAccount . ' ' . $this->steamPassword;
                
                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh steamCmd '.$install . ' "' . trim($this->makeSteamCmd($goFor['sync'])) . '" ' . $this->webhost . ' ' . $this->imageserver . ' ' . $this->steamAccount . ' ' . $this->steamPassword;
                    $tempCmd[] = './control.sh steamCmd '.$install . ' "' . trim($this->makeSteamCmd($goFor['nosync'])) . '" ' . $this->webhost.' none '.$this->steamAccount . ' ' . $this->steamPassword;
                
                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) == 0) {
                    $tempCmd[] = './control.sh steamCmd '.$install . ' "' . trim($this->makeSteamCmd($goFor['sync'])) . '" ' . $this->webhost . ' ' . $this->imageserver . ' ' . $this->steamAccount . ' ' . $this->steamPassword;
                
                } else if (count($goFor['sync']) == 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh steamCmd '.$install . ' "' . trim($this->makeSteamCmd($goFor['nosync'])) . '" ' . $this->webhost.' none '.$this->steamAccount . ' ' . $this->steamPassword;
                }
            }

            // hlds Updates
            if ($hldsCount>0) {
                
                $goFor = $this->$hlds;
                
                foreach (array_unique(array_merge($goFor['sync'], $goFor['nosync'])) as $k) {
                    $query->execute(array($this->rootID, $k));
                    $this->updateIDs[] = $query->fetchColumn();
                }
                
                if ($this->imageserver ==  'none') {
                    $tempCmd[] = './control.sh hldsCmd '.$install . ' "' . implode(' ', array_unique(array_merge($goFor['sync'], $goFor['nosync']))) . '" ' . $this->webhost . ' ' . $this->imageserver;
                
                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh hldsCmd '.$install . ' "' . implode(' ', $goFor['sync']) . '" ' . $this->webhost . ' ' . $this->imageserver;
                    $tempCmd[] = './control.sh hldsCmd '.$install . ' "' . implode(' ', $goFor['nosync']) . '" ' . $this->webhost.' none';
                
                } else if (count($goFor['sync']) > 0 and count($goFor['nosync']) == 0) {
                    $tempCmd[] = './control.sh hldsCmd '.$install . ' "' . implode(' ', $goFor['sync']) . '" ' . $this->webhost . ' ' . $this->imageserver;
                
                } else if (count($goFor['sync']) == 0 and count($goFor['nosync']) > 0) {
                    $tempCmd[] = './control.sh hldsCmd '.$install . ' "' . implode(' ', $goFor['nosync']) . '" ' . $this->webhost.' none';
                }
            }


            // sync maps and addons
            if ($addonCount>0) {
                $tempCmd[] = './control.sh syncaddons '.$this->imageserver . ' "' . implode(' ', $this->maps).'" "'.implode(' ', $this->addons).'"';
            }

            $this->sshcmd = $tempCmd;
        }
        #print_r($this->sshcmd);
        return $this->sshcmd;
    }

    public function setUpdating () {
        
        global $sql;
        
        $query = $sql->prepare("UPDATE `rservermasterg` SET `updating`='Y' WHERE `id`=? LIMIT 1");
        foreach ($this->updateIDs as $id) {
            $query->execute(array($id));
        }
    }

    private function makeSteamCmd ($array) {
        
        $steamCmd = '';
        
        foreach ($array as $key=>$val) {
            
            if (is_numeric($key)) {
                $steamCmd .= $val . ' ' . workAroundForValveChaos($key, $val, false) . ' ';
                
            } else {
                list($appID) = explode('-', $key);
                $steamCmd .= $val . ' ' . workAroundForValveChaos($appID, $val, false) . ' ';
            }
        }

        return $steamCmd;

    }

    function __destruct() {
        unset($this->updateIDs, $this->aeskey, $this->imageserver, $this->resellerID, $this->webhost, $this->rootID, $this->steamAccount, $this->steamPassword, $this->sship, $this->sshport, $this->sshuser, $this->sshpass, $this->publickey, $this->keyname, $this->syncList, $this->steamCmdTotal, $this->steamCmdOutdated, $this->hldsTotal, $this->hldsOutdated, $this->noSteam, $this->maps, $this->addons, $this->sshcmd);
    }
}