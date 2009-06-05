<?php
/**
 * all2eCoupon extension for eZ Publish
 * Written by Norman  Leutner <n.leutner@all2e.com>
 * Copyright (C) 2009. all2e GmbH.  All rights reserved.
 * http://www.all2e.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

define( "EZ_DATATYPESTRING_ALLECOUPON", "all2ecoupon" );

class all2ecouponType extends eZDataType
{
	/*!
	 Construction of the class, note that the second parameter in eZDataType 
	 is the actual name showed in the datatype dropdown list.
	*/
	function all2ecouponType()
	{
	  $this->eZDataType( EZ_DATATYPESTRING_ALLECOUPON, "Coupon", 
	                         array( 'serialize_supported' => true,
	                                'object_serialize_map' => array( 'data_text' => 'text' ) ) );
	}

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
    }

    /*
     validates coupons
    */
    function validateCoupon( $data )
    {
        $db = eZDB::instance();
        $query = "SELECT `couponcode` , `informationcollection_id`
					FROM `all2ecouponcodes`
					WHERE couponcode = '".$data."'
					AND `informationcollection_id` = 0
					AND `contentobject_id` = 0";
        
		$result = $db->arrayQuery( $query );
		
    	if ( count($result) < 1 )
            return false;
            
        return true;
    }

    /*
     marks coupons as used
    */
    function updateCoupon( $type, $data, $id )
    {
        $db = eZDB::instance();
        
	    switch ($type) {
	    case "collection":
	        $query = "UPDATE `all2ecouponcodes` 
	        		  SET `informationcollection_id` = '".$id."' 
	        		  WHERE `couponcode` = '".$data."' LIMIT 1";
	        break;
	    case "object":
        	$query = "UPDATE `all2ecouponcodes` 
        	  		  SET `contentobject_id` = '".$id."' 
        			  WHERE `couponcode` = '".$data."' LIMIT 1";
	        break;
		}

		return $db->query( $query );
    }

    /*
     marks coupons as used
    */
    function updateObjectCoupon( $data, $object )
    {
        $db = eZDB::instance();
        $query = "UPDATE `all2ecouponcodes` 
        			SET `informationcollection_id` = '".$collection->attribute('id')."' 
        			WHERE `couponcode` = '".$data."' LIMIT 1";
               
		return $db->query( $query );
    }
        
    /*
     validates the HTTP input
    */
    function validateStringHTTPInput( $data, $contentObjectAttribute, $classAttribute )
    {
    	if ( !$this->validateCoupon($data ) )
        {
			$contentObjectAttribute->setValidationError(ezi18n( 'extension/all2ecoupon', "Incorrect input. Please try again."));
            return eZInputValidator::STATE_INVALID;
        }
        return eZInputValidator::STATE_ACCEPTED;
    }
    
	/*!
	  Validates the input and returns true if the input was
	  valid for this datatype.
	*/
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
    	if ( $http->hasPostVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute = $contentObjectAttribute->contentClassAttribute();

            if ( $data == "" )
            {
                if ( !$classAttribute->attribute( 'is_information_collector' ) and
                     $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                    
                }
            }
            else
            {
                return $this->validateStringHTTPInput( $data, $contentObjectAttribute, $classAttribute );
            }
        }
        return eZInputValidator::STATE_ACCEPTED;
    }
	
	/*!
	  Validates the input as collected information and returns true if the input was
	  valid for this datatype.
	*/	
	function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
        if ( $http->hasPostVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $classAttribute = $contentObjectAttribute->contentClassAttribute();

            if ( $data == "" )
            {
                if ( $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezi18n( 'kernel/classes/datatypes',
                                                                         'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
                else
                    return eZInputValidator::STATE_ACCEPTED;
            }
            else
            {
                return $this->validateStringHTTPInput( $data, $contentObjectAttribute, $classAttribute );
            }
        }
        else {
        	return eZInputValidator::STATE_INVALID;
        }
	}

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $dataText = $http->postVariable( $base . '_all2ecoupon_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            $contentObjectAttribute->setAttribute( 'data_text', $dataText );
            
            $objectID = $contentObjectAttribute->attribute( 'contentobject_id' );
            $this->updateCoupon( 'object', $dataText, $objectID );
            return true;
        }
        return false;
    }
    	
	/*!
	 Fetches the http post variables for collected information
	*/
	function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
	{
		if ( $http->hasPostVariable( $base . "_all2ecoupon_data_text_" . $contentObjectAttribute->attribute( "id" ) ) )
	    {
	        $dataText = $http->postVariable( $base . "_all2ecoupon_data_text_" . $contentObjectAttribute->attribute( "id" ) );
	        $collectionAttribute->setAttribute( 'data_text', $dataText );
	        
	        $this->updateCoupon( 'collection', $dataText, $collection->attribute('id') );      
	        
	        return true;
	    }
	    return false;
	}

    /*!
     Does nothing since it uses the data_text field in the content object attribute.
     See fetchObjectAttributeHTTPInput for the actual storing.
    */
    function storeObjectAttribute( $attribute )
    {

    }
    	
    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }
    
    /*!
     \return string representation of an contentobjectattribute data for simplified export
    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( 'data_text', $string );
    }
    
    /*!
     Returns the content of the string for use as a title
    */
    function title( $contentObjectAttribute, $name = null )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
    }    
    
	function isIndexable()
	{
	  return false;
	}
	
	function isInformationCollector()
	{
	  return true;
	}

}

eZDataType::register( EZ_DATATYPESTRING_ALLECOUPON, "all2ecouponType" );
