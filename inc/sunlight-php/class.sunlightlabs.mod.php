<?php
/**
 * class SunlightData 
 * PHP Wrapper class around the Sunlight Labs API
 * Docs for the Sunlight API is at http://wiki.sunlightlabs.com/index.php/Main_Page
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class SunlightData {
	
	/**
	 * Type of data request. Either XML or JSON. JSON is strongly recommended but requires PHP 5.2+
	 * @var $type
	 **/
	var $type = 'json';
	
	/**
	 * The Sunlight Labs API URL
	 * @var $request_url
	 **/
	var $request_url = 'http://services.sunlightlabs.com/api/';
	
	/**
	 * Sunlight Labs supplied unique API key. Register at http://services.sunlightlabs.com/api/register/
	 * @var $api_key
	 **/
	var $api_key;
	
	/**
	 * The User Agent of your appplication. It is considered best practice to identify your application when making requests
	 * @var $user_agent
	 **/
	var $user_agent = 'Sunlight Labs-PHP Class/Written by Aaron Brazell [aaron@technosailor.com]';
	
	/**
	 * Not utilized with Sunlight Labs API at this time. Future restrictions may apply.
	 * @var $rate_limit
	 **/
	var $rate_limit = 0;
	
	/**
	 * Utilize this variable if you need to pass other headers in your HTTP request. Must be an array of headers, if used.
	 * @var $headers
	 **/
	var $headers;
	
	/**
	 * Debugging and header return if needed
	 * @var $debug
	 **/
	var $debug = false;
	
	/**
	 * Internal request method. Sends an HTTP request and returns data to the calling method in the form of an object
	 * @access private 
	 * @param string $request required. The dynamically formed API request URL
	 * @return $object
	 **/
	function _request( $request ) 
	{
		return $this->_objectify( $this->_process( $request ) );
	}
	
	/**
	 * Internal helper method for piecing together a query string and urlencoding as needed
	 * @access private
	 * @param array $array required. An array of key/value combinations to pass in a query string
	 * @return string
	 **/
	function _glue( $array )
	{
	    $query_string = '';
	    foreach( $array as $key => $val ) :
			if( is_array( $val ) || is_object( $val ) ) :
				foreach( $val as $skey => $sval ) :
					$query_string .= $key . '=' . rawurlencode( $sval ) . '&';
				endforeach;
			else :
	        	$query_string .= $key . '=' . rawurlencode( $val ) . '&';
			endif;
	    endforeach;
	    
	    return '?apikey=' . $this->api_key . '&' . substr( $query_string, 0, strlen( $query_string )-1 );
	}
	
	/** 
	 * Internal helper method for returning data in the form of an object
	 * @access private
	 * @param string $data required
	 * @return object
	 **/
	function _objectify( $data )
	{
		if( $this->type ==  'json' )
			return json_decode( $data );

		else if( $this->type == 'xml' )
		{
			if( function_exists('simplexml_load_string') ) :
			    $obj = simplexml_load_string( $data );			        
			endif;
			return $obj;
		}
		else
			return false;
	}
	
	/**
	 * An internal method for sending an HTTP request utilizing cURL
	 * @access private
	 * @param string $url required. A properly formed and encoded URL string to send as an HTTP request
	 * @param array $postargs optional. Not currently used with Sunlight Labs API. If provided, the HTTP request is a POST, not GET
	 * @return mixed. If the HTTP request fails, the return value is false. Otherwise, the HTTP Code is returned. i.e. '200'
	 **/
	function _process($url,$postargs=false)
	{
		$ch = curl_init($url);
		if($postargs !== false)
		{
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        if( $this->debug ) :
            curl_setopt($ch, CURLOPT_HEADER, true);
        else :
            curl_setopt($ch, CURLOPT_HEADER, false);
        endif;
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		if( $this->headers )
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        $response = curl_exec($ch);
        
        $this->responseInfo=curl_getinfo($ch);
        curl_close($ch);
        
        
        if( $this->debug ) :
            $debug = preg_split("#\n\s*\n|\r\n\s*\r\n#m", $response);
            echo'<pre>' . $debug[0] . '</pre>'; exit;
        endif;
        
        if( intval( $this->responseInfo['http_code'] ) == 200 )
			return $response;    
        else
            return false;
	}
}

/**
 * class SunlightLegislator 
 * PHP Wrapper class around the Sunlight Labs API
 * Docs for the Sunlight API is at http://wiki.sunlightlabs.com/index.php/Main_Page
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class SunlightLegislator extends SunlightData {
	
	/**
	 * Wrapper around the legislators.getList method documented at http://wiki.sunlightlabs.com/index.php/Legislators.get(List)
	 * @param array $args optional. An array of arguments to be passed. Possible argument keys are:
	 *   title, firstname, middlename, lastname, name_suffix, nickname, party, state, district, in_office,
	 *   gender, phone, fax, website, webform, email, congress_office, bioguide_id, votesmart_id, fec_id,
	 *   govtrack_id, crp_id, eventful_id, sunlight_old_id, congresspedia_id, twitter_id, youtube_url.
	 *   
	 *   Array values can be strings, ints, or arrays (multiple values for same key) - ie. 
	 *     array('firstname' => array('John', 'Hillary'))
	 * For more information, visit wiki.sunlightlabs.com
	 * @return object
	 **/
	function legislatorList( $args = array() )
	{
		$qs = $this->_glue( $args );
		$data = $this->_request( $this->request_url . 'legislators.getList.' . $this->type . $qs );
		return (object) $data->response->legislators;
	}
	
	/**
	 * This method behaves identically to the legislatorList() method except it only returns the first result 
	 * @param array $args optional. See legislatorList() for arguments.
	 * @return object 
	**/
	function legislatorInfo( $args = array() )
	{
		$data = $this->legislatorList( $args );
		foreach( $data->response->legislators as $legislator )
			return $legislator;
	}
	
	/**
	 * A search method that looks for legislators by name.
	 * @param string $name required. The legislators name, i.e. 'John McCain', 'Mikulski', 'A Specter'
	 * @return object 
	**/
	function legislatorSearch( $name )
	{
		if( !is_array( $name ) )
			$name = array( 'name' => $name );
			
		$qs = $this->_glue( $name );
		$data = $this->_request( $this->request_url . 'legislators.search.' . $this->type . $qs );
		return (object) $data->response->results;
	}
	
	/**
	 * A search method for finding which legislators have a district that encompasses a portion of or all of a 5 digit zip code
	 * @param integer $zip required. A five digit zip code. I.e, 21224, 08743
	 * @return object 
	**/
	function legislatorZipCode( $zip )
	{
		if( !is_array( $zip) )
			// $zip = array( 'zip' => (int) $zip ); // commenting out improper type casting
			$zip = array( 'zip' => (string) $zip );
			
		$qs = $this->_glue( $zip );
		$data = $this->_request( $this->request_url . 'legislators.allForZip.' . $this->type . $qs );
		return (object) $data->response->legislators;
	}
}

/**
 * class SunlightDistrict
 * PHP Wrapper class around the Sunlight Labs API
 * Docs for the Sunlight API is at http://wiki.sunlightlabs.com/index.php/Main_Page
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class SunlightDistrict extends SunlightData {
	
	/**
	 * A search method for finding which legislative districts exist in a given 5-digit zip code
	 * @param integer $zip required. A 5-digit zip code to search within. I.e, 21224, 08743
	 * @return object
	 **/
	function districtsByZipCode( $zip )
	{
		if( !is_array( $zip ) )
			$zip = array( 'zip' => (int) $zip );
			
		$qs = $this->_glue( $zip );
		$data = $this->_request( $this->request_url . 'districts.getDistrictsFromZip.' . $this->type . $qs );
		return (object) $data->response->districts;
	}
	
	/**
	 * A search method for finding which zip codes exist within a given legislative district
	 * @param string $state required. A two letter postal-code standardized state abbreviation. I.e 'MD','AK','CA'
	 * @param integer $district_id required. An integer designating which congressional district number. I.e. 3, 12, 1
	 * @return object
	 **/
	function districtsZipCodes( $state, $district_id )
	{
		$args = array( 'state' => (string) $state, 'district' => (int) $district_id );
		$qs = $this->_glue( $args );
		$data = $this->_request( $this->request_url . 'districts.getZipsFromDistrict.' . $this->type . $qs );
		return (object) $data->response->zips;
	}
	
	/**
	 * A search method for finding the legislative district containing designated latitude and longitude coordinates
	 * @param double $latitude required.
	 * @param double $longitude required
	 * @return object 
	**/
	function districtsGeoloc( $latitude, $longitude )
	{
		$qs = $this->_glue( array( 'latitude' => $latitude, 'longitude' => $longitude ) );
		$data = $this->_request( $this->request_url . 'districts.getDistrictFromLatLong.' . $this->type . $qs );
		foreach( $data->response->districts as $district )
			return $district->district;
	}
	
}

/**
 * class SunlightCommittee
 * PHP Wrapper class around the Sunlight Labs API
 * Docs for the Sunlight API is at http://wiki.sunlightlabs.com/index.php/Main_Page
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class SunlightCommittee extends SunlightData {
	
	/**
	 * A search method for finding committees assigned to the House, Senate or Joint chambers
	 * @param string $chamber required. Possible values are House, Senate or Joint
	 * @return object
	 **/
	function committeesList( $chamber )
	{
		if( !is_array( $chamber ) )
			$chamber = array( 'chamber' => $chamber );
			
		$qs = $this->_glue( $chamber );
		$data = $this->_request( $this->request_url . 'committees.getList.' . $this->type . $qs );
		return (object) $data->response->committees;
	}
	
	/**
	 * A method for retriving data for a specific committee
	 * @param string $committee_id required. I.e. 'JSPR'
	 * @return object
	 **/
	function committeesInfo( $committee_id )
	{
		if( !is_array( $committee_id ) )
			$committee_id = array( 'id' => $committee_id );
			
		$qs = $this->_glue( $committee_id );
		$data = $this->_request( $this->request_url . 'committees.get.' . $this->type . $qs );
		return (object) $data->response->committee;
	}
	
	/**
	 * A method to retrieve data about which committees a given legislator is assigned to
	 * @param string $bioguide_id required. I.e. S000148 -You can retrieve the bioguide_id 
	 *   information with the SunlightLegislator::legislatorSearch() method if you don't know
	 * @return object
	 **/
	function committeesLegislator( $bioguide_id )
	{
		if( !is_array( $bioguide_id ) )
			$bioguide_id = array( 'bioguide_id' => $bioguide_id );
			
		$qs = $this->_glue( $bioguide_id );
		$data = $this->_request( $this->request_url . 'committees.allForLegislator.' . $this->type . $qs );
		return (object) $data->response->committees;
	}
}

/**
 * class SunlightLobbyist
 * PHP Wrapper class around the Sunlight Labs API
 * Docs for the Sunlight API is at http://wiki.sunlightlabs.com/index.php/Main_Page
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class SunlightLobbyist extends SunlightData {
	
	/**
	 * A method to retrieve data about a specific lobbyist filing
	 * @param string $filing_id required. A Senate assigned filing id. I.e 29D4D19E-CB7D-46D2-99F0-27FF15901A4C
	 * @return object
	 **/
	function lobbyistFiling( $filing_id )
	{
		if( !is_array( $filing_id ) )
			$filing_id = array( 'id' => $filing_id );
		
		$qs = $this->_glue( $filing_id );
		$data = $this->_request( $this->request_url . 'lobbyists.getFiling.' . $this->type . $qs );
		return (object) $data->response->filing;
	}
	
	/**
	 * A method to retrieve data about a lobbyist
	 * @param string $lobbyist required. The name or organization of either a lobbyist or client
	 * @param boolean $is_registrant optional. If true, $lobbyist is a lobbyist. If false, $lobbyist is a client. Default: true
	 * @param integer $year optional. Restrict results to a 4-digit year. Default: current year
	 * @return object 
	**/
	function lobbyistInfo( $lobbyist, $is_registrant = true, $year = null )
	{
		$lobby = array();
		
		if( !$is_registrant ) :
			$lobby['registrant_name'] = (string) $lobbyist;
		else :
			$lobby['client_name'] = (string) $lobbyist;
		endif;
		
		if( $year )
			$lobby['year'] = (int) $year;
			
		$qs = $this->_glue( $lobby );
		$data = $this->_request( $this->request_url . 'lobbyists.getFilingList.' . $this->type . $qs );
		return (object) $data->response->filings;
	}
	
	/**
	 * A search method for finding data about lobbyists
	 * @param string $search required. A search term(s) relating to the name of a lobbyist
	 * @param integer $year optional. Restricts results to a given search year. Default: current year
	 * @param double $threshold optional. Restricts results to a given "point score" in relaventivity. Default: 0.9
	 * @return object
	 **/
	function lobbyistSearch( $search, $year = null, $threshold = '0.9' )
	{
		if( !$year )
			$year = date('Y');
		
		$qs = $this->_glue( array( 'name' => $search, 'year' => (int) $year, 'threshold' => $threshold ) );
		$data = $this->_request( $this->request_url . 'lobbyists.search.' . $this->type . $qs );
		return (object) $data->response->results;
	}
	
}

/**
 * class OpenSecretsMember
 * PHP Wrapper class around the OpenSecrets API
 * Docs for the OpenSecrets API is at http://www.opensecrets.org/action/api_doc.php
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class OpenSecretsMember extends SunlightData {
	
	/**
	 * A method to retrieve data about Congress members Personal Financial Disclosures
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $year. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function memberDisclosure( $member_ID, $year = null )
	{
		if( !$year )
			$year = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'memPFDprofile', 'year' => $year, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->member_profile;
	}
	
	/**
	 * A method to retrieve data about Congress members privately financed travel
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $year. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function memberTravel( $member_ID, $year = null )
	{
		if( !$year )
			$year = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'memTravelTrips', 'year' => $year, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->trips;
	}
}

/**
 * class OpenSecretsCandidate
 * PHP Wrapper class around the OpenSecrets API
 * Docs for the OpenSecrets API is at http://www.opensecrets.org/action/api_doc.php
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class OpenSecretsCandidate extends SunlightData {
	
	/**
	 * A method to retrieve summary data about a candidate
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $cycle. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function candidateInfo( $member_ID, $cycle = null )
	{
		if( !$cycle )
			$cycle = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'candSummary', 'cycle' => (int) $cycle, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->summary;
	}
	
	/**
	 * A method to retrieve information about campaign contributions from contributors
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $cycle. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function candidateContributors( $member_ID, $cycle = null )
	{
		if( !$cycle )
			$cycle = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'candContrib', 'cycle' => (int) $cycle, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->contributors;
	}
	
	/**
	 * A method to retrieve information about campaign contributions from sectors
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $cycle. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function candidateIndustry( $member_ID, $cycle = null )
	{
		if( !$cycle )
			$cycle = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'candIndustry', 'cycle' => (int) $cycle, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->industries;
	}
	
	/**
	 * A search method to retrieve summary data about contributions to a specified candidate from a specified industry
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param string $industry_ID required. An industry code for a specified industry as provided by data at OpenSecrets. Example: A01
	 * @param integer $cycle. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/
	function candidateIndustryContribution( $member_ID, $industry_ID, $cycle = null )
	{
		if( !$cycle )
			$cycle = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'candIndByInd', 'cycle' => (int) $cycle, 'cid' => $member_ID, 'ind' => $industry_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->candIndus;
	}

	/**
	 * A method to retrieve information about top sector contributions to a specified candidate
	 * @param string $member_ID required. A Member ID as provided by data at OpenSecrets. Example: N00000019
	 * @param integer $cycle. A four-digit calendar year. If not provided, the current year is assumed. Example: 2008
	 * @return object
	 **/	
	function candidateSector( $member_ID, $cycle = null )
	{
		if( !$cycle )
			$cycle = date('Y');
			
		$qs = $this->_glue( array( 'method' => 'candSector', 'cycle' => (int) $cycle, 'cid' => $member_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->sectors;
	}
}

/**
 * class OpenSecretsCommittee
 * PHP Wrapper class around the OpenSecrets API
 * Docs for the OpenSecrets API is at http://www.opensecrets.org/action/api_doc.php
 * @author Aaron Brazell <aaron@technosailor.com>
 * @version 1.0
 * @package sunlightlabs-php
 */
class OpenSecretsCommittee extends SunlightData {
	
	/**
	 * A matrix method to retrieve data about fundraising activities by a specified committee, involving a specific industry, during a specific session of Congress
	 * @param string $committee_ID required. A Committee ID as provided by data at OpenSecrets or the Sunlight Labs API. Example: HARM
	 * @param integer $congress_number required. An integer assigned to the session of Congress. Example: 110
	 * @param string $industry_ID required. An industry code for a specified industry as provided by data at OpenSecrets. Example: A01
	 * @return object
	 **/
	function committeeFundraisingNexus( $committee_ID, $congress_number, $industry_ID )
	{
		$qs = $this->_glue( array( 'method' => 'congCmteIndus', 'congno' => (int) $congress_number, 'indus' => $industry_ID, 'cmte' => $committee_ID, 'output' => $this->type ) );
		$data = $this->_request( $this->request_url . $qs );
		return $data->response->committee;
	}
}
?>