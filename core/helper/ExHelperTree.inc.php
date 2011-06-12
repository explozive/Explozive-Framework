<?php

class ExHelperTree
{
		
	public static function get_children($properties = array())	
	{
		return self::query(array_merge(array('children' => true),$properties));		
	}
	
	
	//
	public static function query($properties)
	{		
		$label_uuid 	= isset($properties['label_uuid']) ? $properties['label_uuid'] : NULL;
		$folder_uuid 	= isset($properties['folder_uuid']) ? $properties['folder_uuid'] : NULL;
		$type_uuid 		= isset($properties['type_uuid']) ? $properties['type_uuid'] : NULL;
		$q 						= isset($properties['q']) ? $properties['q'] : NULL;
		$sortby 			= isset($properties['sortby']) ? preg_replace('/[^a-zA-Z0-9_\s\.,]/i', '-', $properties['sortby']) : '';
		$ancestors 		= isset($properties['ancestors']) ? $properties['ancestors'] : FALSE;
		$children 		= isset($properties['children']) ? $properties['children'] : FALSE;
		$groupby 			= isset($properties['groupby']) ? $properties['groupby'] : NULL;
		
		$conn = new eXDb();	
		$label_uuid 	= isset($properties['label_uuid']) ? $properties['label_uuid'] : NULL;
		$folder_uuid 	= isset($properties['folder_uuid']) ? $properties['folder_uuid'] : NULL;
		$type_uuid 		= isset($properties['type_uuid']) ? $properties['type_uuid'] : NULL;
		$q 						= isset($properties['q']) ? $properties['q'] : NULL;
		$sortby 			= isset($properties['sortby']) ? preg_replace('/[^a-zA-Z0-9_\s\.,]/i', '-', $properties['sortby']) : '';
		$ancestors 		= isset($properties['ancestors']) ? $properties['ancestors'] : FALSE;
		$children 		= isset($properties['children']) ? $properties['children'] : FALSE;
		$groupby 			= isset($properties['groupby']) ? $properties['groupby'] : NULL;
		
		$conn = new eXDb();	
		ob_start();
		?>
			SELECT 
				<?php if($groupby && $groupby == 'label') : ?>
				l.instance_uuid,
				l.instance_name
				<?php elseif($groupby && $groupby == 'type') : ?>
				t.instance_uuid,
				t.instance_name
				<?php else : ?>			
				distinct				
				i.instance_id,
				i.instance_type_id,
				i.instance_parent_id,
				i.instance_url_key,
				i.instance_published,
				i.instance_archived,
				i.instance_secured,
				i.instance_order,
				i.instance_uuid,
				i.instance_name,
				i.instance_content,
				t.instance_name as instance_type_name,					
				r.tree_order		
				<?php endif; ?>	
				<?php if($type_uuid && eXUtils::is_uuid($type_uuid)) : ?>
				, m.username
				<?php endif; ?>	
			FROM 
				<?php print $conn->GetTableName('instance_tree');?> r 
			JOIN 
				<?php print $conn->GetTableName('instance');?> i ON i.instance_id = <?php echo ($ancestors==TRUE) ? 'r.tree_parent_id' : 'r.tree_id';?>	 	
			JOIN 
				<?php print $conn->GetTableName('instance');?> t ON t.instance_id = i.instance_type_id
			LEFT JOIN 
				<?php print $conn->GetTableName('instance');?> f ON f.instance_id = <?php echo ($ancestors==TRUE) ? 'r.tree_id' : 'r.tree_parent_id';?>
				<?php if($children == TRUE) : ?>		
					AND f.instance_id = i.instance_parent_id
				<?php endif; ?>		
			<?php if($label_uuid && (eXUtils::is_uuid($label_uuid) || is_array($label_uuid)) || $groupby == 'label') : ?>
			LEFT JOIN 
				 <?php print $conn->GetTableName('instance_assigned');?> a ON a.instance_id = i.instance_id				
			LEFT JOIN 
				<?php print $conn->GetTableName('instance');?> l ON l.instance_id = a.id_assigned
			<?php endif; ?>	
			<?php if($type_uuid && eXUtils::is_uuid($type_uuid)) : ?>
			LEFT JOIN 
				 <?php print $conn->GetTableName('instance_login');?> m ON m.instance_id = i.instance_id				
			<?php endif; ?>			
			WHERE 
				i.instance_id IS NOT null				
				<?php if($folder_uuid && (eXUtils::is_uuid($folder_uuid) || is_array($folder_uuid))) : ?>					
					<?php if(is_array($folder_uuid)) : ?>
					AND f.instance_uuid IN :folder_uuid
					<?php else : ?>
					AND f.instance_uuid = :folder_uuid
					<?php endif; ?>
				<?php else : ?>
					AND r.tree_parent_id is null
				<?php endif; ?>						
				<?php if($label_uuid && (eXUtils::is_uuid($label_uuid) || is_array($label_uuid))) : ?>
					<?php if(is_array($label_uuid)) : ?>
					AND l.instance_uuid IN :label_uuid
					<?php else : ?>
					AND l.instance_uuid = :label_uuid
					<?php endif; ?>
				<?php endif; ?>
				<?php if($type_uuid && (eXUtils::is_uuid($type_uuid) || is_array($type_uuid))) : ?>
					<?php if(is_array($type_uuid)) : ?>
					AND t.instance_uuid IN :type_uuid
					<?php else : ?>
					AND t.instance_uuid = :type_uuid
					<?php endif; ?>				
				<?php endif; ?>
				<?php if($q && trim($q) != "") : ?>
					AND ( 
						<?php if($type_uuid && eXUtils::is_uuid($type_uuid)) : ?>
						m.username LIKE :username OR
						<?php endif; ?>	
						i.instance_name LIKE :instance_name OR 
						i.instance_url_key LIKE :instance_url_key OR 
						i.instance_uuid LIKE :instance_uuid						
					)
				<?php endif; ?>	
			<?php if($groupby == 'label') : ?>				
					AND l.instance_type_id = :label_type_id
			<?php endif; ?>
			<?php if($groupby) : ?>
			GROUP BY <?php echo (trim($groupby) == 'label') ? 'l.instance_uuid' : 't.instance_uuid'; ?>
			<?php endif; ?>
			ORDER BY 
				<?php if($sortby) echo $sortby . ','; ?> i.instance_id DESC, i.instance_name ASC	
		<?php			
		$query = ob_get_clean();
		
		//if(isset($properties['test']))
			//echo $query;
		$sth = $conn->statement($query);
		$sth->bind_param(':label_uuid', $label_uuid);
		$sth->bind_param(':type_uuid', $type_uuid);
		$sth->bind_param(':folder_uuid', $folder_uuid);		
		
		if($groupby)
			$sth->bind_param(':label_type_id', eXQueryInstance::uuid_to_id(EX_UUID_TYPE_LABEL));
		
		if($q && trim($q) != "")
		{
			$q = '%' . $q . '%';
			$sth->bind_param(':instance_name', $q);
			$sth->bind_param(':instance_url_key', $q);	
			$sth->bind_param(':username', $q);
			$sth->bind_param(':instance_uuid', $q);		
		}
		
		
		$res = $sth->query();
		
		return ($res) ? $res->fetch(eXDb::FETCH_MODE_ALL) : array();
	}
	
	
	
}