<?php

// Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

if (empty($extrafieldsobjectkey) && is_object($object)) $extrafieldsobjectkey=$object->table_element;

// Loop to show all columns of extrafields for the search title line
if (! empty($extrafieldsobjectkey))	// $extrafieldsobject is the $object->table_element like 'societe', 'socpeople', ...
{
	if (is_array($extrafields->attributes[$extrafieldsobjectkey]['label']) && count($extrafields->attributes[$extrafieldsobjectkey]['label']))
	{
		foreach($extrafields->attributes[$extrafieldsobjectkey]['label'] as $key => $val)
		{
			if (! empty($arrayfields["ef.".$key]['checked'])) {
			    if (in_array($key, array('serv_date_start', 'serv_date_end', 'serv_duree'))) {
			        switch ($key) {
                        case 'serv_date_start':
                            //Date debut
                            print '<td class="liste_titre" align="center" style=" width: 200px;">';
                            $arrayofoperators = array('<' => '<', '>' => '>');
                            print $form->selectarray('filter_opouvertureprevue', $arrayofoperators, $filter_opouvertureprevue, 1);
                            print ' ';
                            $filter_dateouvertureprevue = dol_mktime(0, 0, 0, $opouvertureprevuemonth, $opouvertureprevueday, $opouvertureprevueyear);
                            // Wrong dol_mktime means that everything is set to 0
                            if ($filter_dateouvertureprevue < 0 || ($opouvertureprevuemonth == '00' && $opouvertureprevueday == '00' && $opouvertureprevueyear == '00'))
                                $filter_dateouvertureprevue = '';
                            print $form->select_date($filter_dateouvertureprevue, 'dateouvertureprevue', 0, 0, 1, '', 1, 0, 1);
                            print '</td>';
                            break;
                        case 'serv_duree':
                            // Service duree
                            print '<td class="liste_titre" align="center" style=" width: 220px;">';
                            print $extrafields->showInputField('serv_duree', $options_serv_duree);

                            print '&nbsp;</td>';
                            break;
                        case 'serv_date_end':
                            // Date cloture
                            print '<td class="liste_titre" align="center" style=" width: 200px;">';
                            $arrayofoperators = array('<' => '<', '>' => '>');
                            print $form->selectarray('filter_opclotureprevue', $arrayofoperators, $filter_opclotureprevue, 1);
                            print ' ';
                            $filter_dateclotureprevue = dol_mktime(0, 0, 0, $opclotureprevuemonth, $opclotureprevueday, $opclotureprevueyear);
                            // Wrong dol_mktime means that everything is set to 0
                            if ($filter_dateclotureprevue < 0 || ($opclotureprevuemonth == '00' && $opclotureprevueday == '00' && $opclotureprevueyear == '00'))
                                $filter_dateclotureprevue = '';
                            print $form->select_date($filter_dateclotureprevue, 'dateclotureprevue', 0, 0, 1, '', 1, 0, 1);
                            print '</td>';
                            break;
                        default :
                            print '<td></td>';
                            break;
                    }
                } else {
                    $align=$extrafields->getAlignFlag($key);
                    $typeofextrafield=$extrafields->attributes[$extrafieldsobjectkey]['type'][$key];
                    print '<td class="liste_titre'.($align?' '.$align:'').'">';
                    if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')) && empty($extrafields->attributes[$extrafieldsobjectkey]['computed'][$key]))
                    {
                        $crit=$val;
                        $tmpkey=preg_replace('/search_options_/','',$key);
                        $searchclass='';
                        if (in_array($typeofextrafield, array('varchar', 'select'))) $searchclass='searchstring';
                        if (in_array($typeofextrafield, array('int', 'double'))) $searchclass='searchnum';
                        print '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
                    }
                    elseif (! in_array($typeofextrafield, array('datetime','timestamp')))
                    {
                        // for the type as 'checkbox', 'chkbxlst', 'sellist' we should use code instead of id (example: I declare a 'chkbxlst' to have a link with dictionnairy, I have to extend it with the 'code' instead 'rowid')
                        $morecss='';
                        if ($typeofextrafield == 'sellist') $morecss='maxwidth200';
                        echo $extrafields->showInputField($key, $search_array_options['search_options_'.$key], '', '', 'search_', $morecss);
                    }
                    elseif (in_array($typeofextrafield, array('datetime','timestamp')))
                    {
                    }
                    print '</td>';
                }
			}
		}
	}
}