<?php
class sqliCostumRestContentController extends ezpRestMvcController
{

	const VIEWLIST_RESPONSEGROUP_METADATA = 'Metadata';
	    const VIEWCONTENT_RESPONSEGROUP_METADATA = 'Metadata',
          VIEWCONTENT_RESPONSEGROUP_LOCATIONS = 'Locations',
          VIEWCONTENT_RESPONSEGROUP_FIELDS = 'Fields'; 
    
    public function doViewContent()
    {
        $this->setDefaultResponseGroups( array( self::VIEWCONTENT_RESPONSEGROUP_METADATA ) );
        $isNodeRequested = false;
        if ( isset( $this->nodeId ) )
        {
            $content = ezpContent::fromNodeId( $this->nodeId );
            $isNodeRequested = true;
        }
        elseif ( isset( $this->objectId ) )
        {
            $content = ezpContent::fromObjectId( $this->objectId );
        }

        $result = new ezpRestMvcResult();

        // translation parameter
        if ( $this->hasContentVariable( 'Translation' ) )
            $content->setActiveLanguage( $this->getContentVariable( 'Translation' ) );

        // Handle metadata
        if( $this->hasResponseGroup( self::VIEWCONTENT_RESPONSEGROUP_METADATA ) )
        {
            $objectMetadata = ezpRestContentModel::getMetadataByContent( $content );
            if( $isNodeRequested )
            {
                $nodeMetadata = ezpRestContentModel::getMetadataByLocation( ezpContentLocation::fetchByNodeId( $this->nodeId ) );
                $objectMetadata = array_merge( $objectMetadata, $nodeMetadata );
            }
            $result->variables['metadata'] = $objectMetadata;
        }
        
        // Handle locations if requested
        if( $this->hasResponseGroup( self::VIEWCONTENT_RESPONSEGROUP_LOCATIONS ) )
        {
            $result->variables['locations'] = ezpRestContentModel::getLocationsByContent( $content );
        }
         
        // Handle fields content if requested
        if( $this->hasResponseGroup( self::VIEWCONTENT_RESPONSEGROUP_FIELDS ) )
        {
            $result->variables['fields'] = ezpRestContentModel::getFieldsByContent( $content );
        }
        
        // Add links to fields resources
        $result->variables['links'] = ezpRestContentModel::getFieldsLinksByContent( $content, $this->request );

        if ( $outputFormat = $this->getContentVariable( 'OutputFormat' ) )
        {
            $renderer = ezpRestContentRenderer::getRenderer( $outputFormat, $content, $this );
            $result->variables['renderedOutput'] = $renderer->render();
        }

        return $result;
    }
	
	
	public function doList()
    {
        $this->setDefaultResponseGroups( array( self::VIEWLIST_RESPONSEGROUP_METADATA ) );
        $result = new ezpRestMvcResult();
        $crit = new ezpContentCriteria();

        // Location criteria
        // Hmm, the following sequence is too long...
        $crit->accept[] = ezpContentCriteria::location()->subtree( ezpContentLocation::fetchByNodeId( $this->nodeId ) );
        
        $crit->accept[] = ezpContentCriteria::depth( 1 ); // Fetch children only
        
        // class criteria
        
        if(isset( $this->class )) $crit->accept[] = ezpContentCriteria::contentClass()->is( $this->class );
       
        
        // Limit criteria
        $offset = isset( $this->offset ) ? $this->offset : 0;
        $limit = isset( $this->limit ) ? $this->limit : 10;
        $crit->accept[] = ezpContentCriteria::limit()->offset( $offset )->limit( $limit );
        
        // Sort criteria
        if( isset( $this->sortKey ) )
        {
            $sortOrder = isset( $this->sortType ) ? $this->sortType : 'asc';
            $crit->accept[] = ezpContentCriteria::sorting( $this->sortKey, $sortOrder );
        }

        $result->variables['childrenNodes'] = ezpRestContentModel::getChildrenList( $crit, $this->request, $this->getResponseGroups() );
        // REST links to children nodes
        // Little dirty since this should belong to the model layer, but I don't want to pass the router nor the full controller to the model
        $contentQueryString = $this->request->getContentQueryString( true );
         
        for( $i = 0, $iMax = count( $result->variables['childrenNodes'] ); $i < $iMax; ++$i )
        {
            $linkURI = $this->getRouter()->generateUrl( 'ezpNode', array( 'nodeId' => $result->variables['childrenNodes'][$i]['nodeId'] ) );
            $result->variables['childrenNodes'][$i]['link'] = $this->request->getHostURI().$linkURI.$contentQueryString;
        }
        
        // Handle Metadata
        if( $this->hasResponseGroup( self::VIEWLIST_RESPONSEGROUP_METADATA ) )
        {
            $childrenCount = ezpRestContentModel::getChildrenCount( $crit );
            $result->variables['metadata'] = array(
                'childrenCount' => $childrenCount,
                'parentNodeId'  => $this->nodeId
            );
            
        }
        
        return $result;
    }
}