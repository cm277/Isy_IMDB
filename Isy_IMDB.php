<?php
// PHP IMDB Scraper API by Islander 
// Version: 1.0
require_once(__DIR__.DIRECTORY_SEPARATOR.'countryarray.php');

class Isy_IMDB
{  
    public function getMovieInfo($title, $id = true)
    {
        $arr = array();
		
		if($title === NULL){
            $arr['error'] = "No Title found in Search Results!";
            return $arr;
        }
		
		$imdbUrl = ($id == true) ? $title : $this->getIMDbIdFromGoogle($title);
        
		$imdbUrl = "http://akas.imdb.com/title/" . trim($imdbUrl) . "/";
		
        $html = $this->getURL($imdbUrl);
		
        if(stripos($html, "<meta name=\"application-name\" content=\"IMDb\" />") !== false){
            $arr = $this->ScrapMovieInfo($html);
        } else {
            $arr['error'] = "No Title found on IMDb!";
        }
		
        return $arr;
    }
    
	private function getIMDbIdFromGoogle($title){
        $url = "http://www.google.com/search?q=imdb+" . rawurlencode($title);
        $html = $this->getURL($url);
        $ids = $this->match_all('/<a href="http:\/\/www.imdb.com\/title\/(tt\d+).*?".*?>.*?<\/a>/ms', $html, 1);
        if (!isset($ids[0])) //if Google fails
            return $this->getIMDbIdFromBing($title); //search using Bing
        else
            return $ids[0]; //return first IMDb result
    }
    
    private function getIMDbIdFromBing($title){
        $url = "http://www.bing.com/search?q=imdb+" . rawurlencode($title);
        $html = $this->getURL($url);
        $ids = $this->match_all('/<a href="http:\/\/www.imdb.com\/title\/(tt\d+).*?".*?>.*?<\/a>/ms', $html, 1);
        if (!isset($ids[0]))
            return NULL;
        else
            return $ids[0]; //return first IMDb result
    }
    
	public function getMovieMiniInfo($title, $id = true)
    {
        $arr = array();
		
		if($title === NULL){
            $arr['error'] = "No Title found in Search Results!";
            return $arr;
        }
		
		$imdbUrl = ($id == true) ? $title : $this->getIMDbIdFromGoogle($title);
        
		$imdbUrl = "http://akas.imdb.com/title/" . trim($imdbUrl) . "/";
		
        $html = $this->getURL($imdbUrl);
		
        if(stripos($html, "<meta name=\"application-name\" content=\"IMDb\" />") !== false){
            $arr = $this->ScrapMovieMiniInfo($html);
        } else {
            $arr['error'] = "No Title found on IMDb!";
        }
		
        return $arr;
    }
	
	private function ScrapMovieMiniInfo($html)
    {
        $arr = array();
        $arr['mid'] = $this->match('/<link rel="canonical" href="http:\/\/www.imdb.com\/title\/(tt[0-9]+)\/" \/>/ms', $html, 1);
        $arr['title'] = trim($this->match('/<h1 class="header" itemprop="name">\n(.*?)\n(<span|<\/h1>)/ms', $html, 1));
        
        $arr['year'] = trim($this->match('/<title>.*?\(.*?(\d{4}).*?\).*?<\/title>/ms', $html, 1));
        $arr['rating'] = $this->match('/ratingValue">(\d.\d)</ms', $html, 1);

        $arr['mpaa'] = trim($this->match('/infobar">.<img.*?alt="(.*?)".*?>/ms', $html, 1));
        
		$rls_date = $this->match('/Release Date:<\/h4>.*?(\d{2}? (January|February|March|April|May|June|July|August|September|October|November|December) (19|20)\d{2}).*?(\(|<span)/ms', $html, 1);
		$arr['releasedate'] = strtotime($rls_date);
		
        $arr['runtime'] = trim($this->match('/Runtime:<\/h4>.*?(\d+) min.*?<\/div>/ms', $html, 1));
        if($arr['runtime'] == '') $arr['runtime'] = trim($this->match('/infobar.*?(\d+) min.*?<\/div>/ms', $html, 1));

        $arr['votes'] = str_replace(",", "", $this->match('/ratingCount">(\d+,?\d*)<\/span>/ms', $html, 1));
        $arr['budget'] = str_replace(",", "", trim($this->match('/Budget:<\/h4>.*?(\d+,?\d+,\d*)(.*?)<\/div>/ms', $html, 1)));
		$arr['gross'] = str_replace(",", "", trim($this->match('/Gross:<\/h4>.*?(\d+,?\d+,\d*)(.*?)<\/div>/ms', $html, 1)));
		$arr['reviewedusers'] = str_replace(",", "", trim($this->match('/span itemprop="reviewCount">(\d+)<\/span> user<\/a>/ms', $html, 1)));
		$arr['mcrating'] = str_replace(",", "", trim($this->match('/href="criticreviews">(\d+)\/100<\/a>/ms', $html, 1)));
		$arr['mcreviewedusers'] = str_replace(",", "", trim($this->match('/>(\d+)<\/a> from <a/ms', $html, 1)));
		$arr['website'] = trim($this->match('/Official Sites:<\/h4>\n<a href="(.*?)">.*?<\/a>/ms', $html, 1));

        return $arr;
		
    }
	
    // Scan movie meta data from IMDb page
    private function scrapMovieInfo($html)
    {
        $arr = array();
        $arr['mid'] = $this->match('/<link rel="canonical" href="http:\/\/www.imdb.com\/title\/(tt\d+)\/" \/>/ms', $html, 1);
        $arr['title'] = trim($this->match('/<h1 class="header" itemprop="name">\n(.*?)\n(<span|<\/h1>)/ms', $html, 1));
        
        $arr['year'] = trim($this->match('/<title>.*?\(.*?(\d{4}).*?\).*?<\/title>/ms', $html, 1));
        $arr['rating'] = $this->match('/ratingValue">(\d.\d)/ms', $html, 1);
       
	    $arr['genres'] = array();
        foreach($this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Genre.?:(.*?)(<\/div>|See more)/ms', $html, 1), 1) as $m)
            array_push($arr['genres'], trim($m));

        $arr['mpaa'] = trim($this->match('/infobar">.<img.*?alt="(.*?)".*?>/ms', $html, 1));
        
		$rls_date = $this->match('/Release Date:<\/h4>.*?(\d{2}? (January|February|March|April|May|June|July|August|September|October|November|December) (19|20)\d{2}).*?(\(|<span)/ms', $html, 1);
        
		$arr['releasedate'] = strtotime($rls_date);
		
		/*//Get extra inforation on  Release Dates and AKA Titles
        if($arr['mid'] != ""){
		
            $releaseinfoHtml = $this->getURL("http://akas.imdb.com/title/" . $arr['mid'] . "/releaseinfo");
			
			$arr['release_dates'] = $this->getReleaseDates($releaseinfoHtml);
			
        }
		*/
		
        $arr['poster'] = trim($this->match('/img_primary">.*?<img src="(.*?)".*?<\/td>/ms', $html, 1));
        
        if ($arr['poster'] != '' && strrpos($arr['poster'], "nopicture") === false && strrpos($arr['poster'], "ad.doubleclick") === false) { //Get large and small posters
            $arr['poster'] = substr($arr['poster'], 0, strrpos($arr['poster'], "_V1.")) . "_V1._SY500.jpg";
        } else {
            $arr['poster'] = "";
        }
		
        $arr['runtime'] = trim($this->match('/Runtime:<\/h4>.*?(\d+) min.*?<\/div>/ms', $html, 1));
        if($arr['runtime'] == '') $arr['runtime'] = trim($this->match('/infobar.*?(\d+) min.*?<\/div>/ms', $html, 1));
        
		/*$arr['top_250'] = trim($this->match('/Top 250 #(\d+)</ms', $html, 1));
        $arr['oscars'] = trim($this->match('/Won (\d+) Oscars./ms', $html, 1));
        $arr['awards'] = trim($this->match('/(\d+) wins/ms',$html, 1));
        $arr['nominations'] = trim($this->match('/(\d+) nominations/ms',$html, 1));*/
		
        $arr['storyline'] = trim(strip_tags($this->match('/Storyline<\/h2>(.*?)(<em|<\/p>|<span)/ms', $html, 1)));
        //$arr['tagline'] = trim(strip_tags($this->match('/Tagline.?:<\/h4>(.*?)(<span|<\/div)/ms', $html, 1)));
        $arr['votes'] = str_replace(",", "", $this->match('/href="ratings".*?><span itemprop="ratingCount">(\d+,?\d*)<\/span> users<\/a>/ms', $html, 1));
		
		$arr['reviewedusers'] = str_replace(",", "", trim($this->match('/span itemprop="reviewCount">(\d+)<\/span> user<\/a>/ms', $html, 1)));
		$arr['mcrating'] = str_replace(",", "", trim($this->match('/href="criticreviews">(\d+)\/100<\/a>/ms', $html, 1)));
		$arr['mcreviewedusers'] = str_replace(",", "", trim($this->match('/>(\d+)<\/a> from <a/ms', $html, 1)));
        
		$arr['language'] = array();
        foreach($this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Language.?:(.*?)(<\/div>|>.?and )/ms', $html, 1), 1) as $m)
            array_push($arr['language'], trim($m));
		
		/*	
        $arr['country'] = array();
        foreach($this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Country:(.*?)(<\/div>|>.?and )/ms', $html, 1), 1) as $c)
            array_push($arr['country'], trim($c));
		
        if($arr['mid'] != "") $arr['media_images'] = $this->getMediaImages($arr['mid']);
		*/
		
        $arr['budget'] = str_replace(",", "", trim($this->match('/Budget:<\/h4>.*?(\d+,?\d+,\d*)(.*?)<\/div>/ms', $html, 1)));
		$arr['gross'] = str_replace(",", "", trim($this->match('/Gross:<\/h4>.*?(\d+,?\d+,\d*)(.*?)<\/div>/ms', $html, 1)));
		$arr['website'] = trim($this->match('/Official Sites:<\/h4>\n<a href="(.*?)">.*?<\/a>/ms', $html, 1));
		
        $arr['production_co'] = array();
        foreach($this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/Production Co:(.*?)(<span|<\/span>|.?and )/ms', $html, 1), 1) as $p)
			array_push($arr['production_co'], trim($p));

        return $arr;
		
    }
    
	// Start -> SY full credits function
	public function getFullCredits($titleId) {
	
		$url  = "http://akas.imdb.com/title/" . $titleId . "/fullcredits";
		$html = $this->getURL($url);
		$full_credits = array();
		
		$full_credits['full_directors'] = array();
        foreach($this->extra_match_all('/<td valign="top"><a href="\/name\/nm+([\d]{7})\/">(.*?)<\/a><\/td><td valign="top">.*?<\/td><td valign="top">(.*?)<\/td>/ms', $this->match('/Directed by<\/a><\/h5>.*?<\/td><\/tr>(.*?)<\/table>/ms', $html, 1)) as $fwdr)
        {
            array_push($full_credits['full_directors'], $fwdr);
        }
		
		$full_credits['full_directors'] = $this->make_multidimension_array($full_credits['full_directors'][0],$full_credits['full_directors'][1],$full_credits['full_directors'][2]);
		
		$full_credits['full_writers'] = array();
        foreach($this->extra_match_all('/<td valign="top"><a href="\/name\/nm+([\d]{7})\/">(.*?)<\/a><\/td><td.*?valign="top">(.*?)(<br><br>)?<\/td>/ms', $this->match('/Writing credits<\/a><\/h5>.*?<\/td><\/tr>(.*?)<\/table>/ms', $html, 1)) as $fw)
        {
            array_push($full_credits['full_writers'], $fw);
        }
		
		$full_credits['full_writers'] = $this->make_multidimension_array($full_credits['full_writers'][0],$full_credits['full_writers'][1],$full_credits['full_writers'][2]);
		
		$full_credits['full_cast'] = array();
		foreach($this->extra_match_all('/<td class="nm"><a href="\/name\/nm+([\d]{7})\/" onclick.*?>(.*?)<\/a><\/td><td class="ddd">.*?<\/td><td class="char">(<a href="\/character\/ch+([\d]{7})\/">)?(.*?)(<\/a>)?<\/td>/ms', $this->match('/<table class="cast">(.*?)<\/table>/ms', $html, 1), true) as $fxx)
		{
			array_push($full_credits['full_cast'], $fxx);
		}
		
		$full_credits['full_cast'] = $this->make_multidimension_array($full_credits['full_cast'][0],$full_credits['full_cast'][1],$full_credits['full_cast'][2]);
		
		$full_credits['original_music'] = array();
        foreach($this->extra_match_all('/<td valign="top"><a href="\/name\/nm+([\d]{7})\/">(.*?)<\/a><\/td><td valign="top">.*?<\/td><td valign="top">(.*?)<\/td>/ms', $this->match('/Original Music by<\/a><\/h5><\/td><\/tr>(.*?)<\/table>/ms', $html, 1)) as $om)
        {
            array_push($full_credits['original_music'], $om);
        }
		
		$full_credits['original_music'] = $this->make_multidimension_array($full_credits['original_music'][0],$full_credits['original_music'][1],$full_credits['original_music'][2]);
		
		/*
		$full_credits['cinematography'] = array();
        foreach($this->extra_match_all('/<td valign="top"><a href="\/name\/nm+([\d]{7})\/">(.*?)<\/a><\/td><td valign="top">.*?<\/td><td valign="top">(.*?)<\/td>/ms', $this->match('/Cinematography by<\/a><\/h5><\/td><\/tr>(.*?)<\/table>/ms', $html, 1)) as $c)
        {
            array_push($full_credits['cinematography'], $c);
        }
		
		$full_credits['cinematography'] = $this->make_multidimension_array($full_credits['cinematography'][0],$full_credits['cinematography'][1],$full_credits['cinematography'][2]);
		
		$full_credits['art_direction'] = array();
        foreach($this->extra_match_all('/<td valign="top"><a href="\/name\/nm+([\d]{7})\/">(.*?)<\/a><\/td><td valign="top">.*?<\/td><td valign="top">(.*?)<\/td>/ms', $this->match('/Art Direction by<\/a><\/h5><\/td><\/tr>(.*?)<\/table>/ms', $html, 1)) as $ad)
        {
            array_push($full_credits['art_direction'], $ad);
        }
		
		$full_credits['art_direction'] = $this->make_multidimension_array($full_credits['art_direction'][0],$full_credits['art_direction'][1],$full_credits['art_direction'][2]);
		*/
		
		return $full_credits;
		
	}
	
	private function make_multidimension_array($ar0, $ar1 , $ar2, $appendwt="nm") {
		
		if (count($ar0) != count($ar1)) {
			return;
		}
		
		if (count($ar0) != count($ar2)) {
			return;
		}
		
		$tmparr = array();
		foreach($ar0 as $num => $value) {
			$tmparr[$appendwt.$value] = array('name'=>$ar1[$num],'as'=>$ar2[$num]);
		}
		
		return $tmparr;
	
	}
	
    // Scan all Release Dates
    private function getReleaseDates($html){
		global $country_array;
		
        $releaseDates = array();
        foreach($this->match_all('/<tr>(.*?)<\/tr>/ms', $this->match('/Date<\/th><\/tr>(.*?)<\/table>/ms', $html, 1), 1) as $r)
        {
            $country = trim(strip_tags($this->match('/<td><b>(.*?)<\/b><\/td>/ms', $r, 1)));
			$ccode = array_search($country, $country_array);
			
			if ($ccode != "") {
			
				$dt = trim(strip_tags($this->match('/<td align="right">(.*?)<\/td>/ms', $r, 1)));
				$date = strtotime($dt);
				
				if(!in_array($ccode,$releaseDates)) {
				
					$releaseDates[$ccode] = $date;
				
				} elseif(in_array($ccode,$releaseDates)) {
					
					if($releaseDates[$ccode] < $date)
					array_replace($releaseDates, array($releaseDates[$ccode] => $date));
					
				}
				
			}
        }
		
		asort($releaseDates);
		
        return $releaseDates;
		
    }
 
    // Collect all Media Images
    private function getMediaImages($titleId){
        $url  = "http://akas.imdb.com/title/" . $titleId . "/mediaindex";
        $html = $this->getURL($url);
        $media = array();
        $media = array_merge($media, $this->scanMediaImages($html));
        foreach($this->match_all('/<a href="\?page=(.*?)">/ms', $this->match('/<span style="padding: 0 1em;">(.*?)<\/span>/ms', $html, 1), 1) as $p)
        {
            $html = $this->getURL($url . "?page=" . $p);
            $media = array_merge($media, $this->scanMediaImages($html));
        }
        return $media;
    }
 
    // Scan all media images
    private function scanMediaImages($html){
        $pics = array();
        foreach($this->match_all('/src="(.*?)"/ms', $this->match('/<div class="thumb_list" style="font-size: 0px;">(.*?)<\/div>/ms', $html, 1), 1) as $i)
        {
            $i = substr($i, 0, strrpos($i, "_V1.")) . "_V1._SY500.jpg";
            array_push($pics, $i);
        }
        return $pics;
    }
	
	public function getPersonInfo($pId) {
	
		$parr = array();
		
		if($pId === NULL){
            $parr['p_error'] = "No Person found in Search Results!";
            return $parr;
        }
        
		$pUrl = "http://akas.imdb.com/name/" . trim($pId) . "/";
		
        $phtml = $this->getURL($pUrl);
		
        if(stripos($phtml, "<meta name=\"application-name\" content=\"IMDb\" />") !== false){
            $parr = $this->ScrapPersonInfo($phtml, $pId);
            $parr['p_id'] = trim($pId);
			$parr['p_url'] = $pUrl;
        } else {
            $parr['p_error'] = "No Person found on IMDb!";
        }
		
        return $parr;
	
	}
	
	private function ScrapPersonInfo($phtml, $pId) {
	
		$parr = array();

        $parr['p_name'] = trim($this->match('/<h1 class="header" itemprop="name">(.*?)\n(<span|<\/h1>)/ms', $phtml, 1));
		$parr['p_photo'] = trim($this->match('!<td id="img_primary".*?>\s*.*?<img.*?src="(.*?)"!ims',$phtml,1));
		
		if ($parr['p_photo'] != '' && strrpos($parr['p_photo'], "nopicture") === false && strrpos($parr['p_photo'], "ad.doubleclick") === false) {
            $parr['p_photo'] = $parr['p_photo'];
        } else {
            $parr['p_photo'] = '';
        }
		
		$parr['p_genres'] = array();
        foreach($this->match_all('/<a.*?>(.*?)<\/a>/ms', $this->match('/<div class="infobar">(.*?)<\/div>/ms', $phtml, 1), 1) as $pg)
            array_push($parr['p_genres'], trim($pg));
		
		$parr['p_website'] = trim($this->match('/Official Sites:<\/h4>\n<a href="(.*?)">.*?<\/a>/ms', $phtml, 1));
		
		if ( preg_match('/<div class="article highlighted" >(.*?)<span class="see-more inline">.*<\/div>/ms',$phtml,$match) ) {
			
			$parr['p_awards'] = trim(str_replace("<b>","",str_replace("</b>"," ",str_replace("&amp;","&",str_replace("\r\n","",str_replace("\n","",trim($match[1])))))));
		
		}
		
		$purl  = "http://akas.imdb.com/name/" . $pId . "/bio";
		$pbiohtml = $this->getURL($purl);
		
		$parr['p_birthname'] = trim($this->match("/Birth Name<\/h5>\s*\n(.*?)\n/m",$pbiohtml,1));
		
		/*$parr['p_nicknames'] = array();
        foreach(explode("<br/>", $this->match('/Nickname<\/h5>\s*\n(.*?)\n<h5>/ms', $pbiohtml, 1)) as $pn) {
			
			$pnick = trim($pn);
			if (!empty($pnick)) array_push($parr['p_nicknames'], $pnick);
			
		}*/
		
		if ( preg_match('|Date of Birth</h5>\s*(.*)<br|iUms',$pbiohtml,$match) ) {
		
			preg_match('|/date/(\d+)-(\d+)/.*?>\d+\s+(.*?)<|',$match[1],$daymon);
			preg_match('|/search/name\?birth_year=(\d{4})|ims',$match[1],$dyear);
			preg_match('|/search/name\?birth_place=.*?">(.*)<|ims',$match[1],$dloc);
			
			if(isset($dyear[1]) && isset($daymon[2]) && isset($daymon[3]) && $dyear[1] != '' && $daymon[2] != '' && $daymon[3] != '') {
				$parr['p_birthdate'] = strtotime($daymon[2]." ".$daymon[3]." ".$dyear[1]);
			} elseif(isset($dyear[1]) && isset($daymon[3]) && empty($daymon[2]) && $dyear[1] != '' && $daymon[3] != '' && $daymon[2] == '') {
				$parr['p_birthdate'] = strtotime($daymon[3]." ".$dyear[1]);
			} elseif(isset($dyear[1]) && empty($daymon[3]) && empty($daymon[2]) && $dyear[1] != '' && $daymon[2] == '' && $daymon[3] == '') {
				$parr['p_birthdate'] = strtotime($dyear[1]);
			} elseif(empty($dyear[1]) && empty($daymon[3]) && empty($daymon[2]) && $dyear[1] == '' && $daymon[2] == '' && $daymon[3] == '') {
				$parr['p_birthdate'] = '';
			}

			$parr['p_birthplace'] = (isset($dloc[1]) && trim($dloc[1]) != '') ? trim($dloc[1]) : '';
		
		}
		
		if (preg_match('|Date of Death</h5>(.*)<br|iUms',$pbiohtml,$match)) {
		
			preg_match('|/date/(\d+)-(\d+)/.*?>\d+\s+(.*?)<|',$match[1],$ddaymon);
			preg_match('|/search/name\?death_date=(\d{4})|ims',$match[1],$ddyear);
			preg_match('/(\,\s*([^\(]+))/ims',$match[1],$ddloc);
			preg_match('/\(([^\)]+)\)/ims',$match[1],$ddcause);
			
			if(isset($ddyear[1]) && isset($ddaymon[3]) && isset($ddaymon[2]) && $ddyear[1] != '' && $ddaymon[2] != '' && $ddaymon[3] != '') {
				$parr['p_deathdate'] = strtotime($ddaymon[2]." ".$ddaymon[3]." ".$ddyear[1]);
			} elseif(isset($ddyear[1]) && isset($ddaymon[3]) && empty($ddaymon[2]) && $ddyear[1] != '' && $ddaymon[3] != '' && $ddaymon[2] == '') {
				$parr['p_deathdate'] = strtotime($ddaymon[3]." ".$ddyear[1]);
			} elseif(isset($ddyear[1]) && empty($ddaymon[3]) && empty($ddaymon[2]) && $ddyear[1] != '' && $ddaymon[2] == '' && $ddaymon[3] == '') {
				$parr['p_deathdate'] = strtotime($ddyear[1]);
			} elseif(empty($ddyear[1]) && empty($ddaymon[3]) && empty($ddaymon[2]) && $ddyear[1] == '' && $ddaymon[2] == '' && $ddaymon[3] == '') {
				$parr['p_deathdate'] = '';
			}

			$parr['p_deathplace'] = (isset($ddloc[2]) && trim($ddloc[2]) != '') ? trim($ddloc[2]) : '';
			$parr['p_deathreason'] = (isset($ddcause[1]) && trim($ddcause[1]) != '') ? trim($ddcause[1]) : '';
		
		}
		
		if (preg_match("/Height<\/h5>\s*\n(.*?)\s*\((.*?)\)/m",$pbiohtml,$match)) {
		
			$parr['p_height'] = trim($match[1]) . " (" . trim($match[2]) . ")";
	  
		}
		
		if (preg_match('/<h5>Mini Biography<\/h5>\n<p>(.*?)<\/p>/ms',$pbiohtml,$matches)) {
		
			$bio_bio = str_replace("href=\"/name/nm","href=\"http://akas.imdb.com/name/nm",
                              str_replace("href=\"/title/tt","href=\"http://akas.imdb.com/title/tt",
                                str_replace('/SearchBios','http://akas.imdb.com/SearchBios',
								str_replace('href=\"/search/name?bio=award"','http://akas.imdb.com/search/name?bio=award',$matches[1]))));

				
			$parr['p_bio'] = trim($bio_bio);

		}
		
		return $parr;
	
	}
	
    // ************************[ Extra Functions ]******************************
    private function getURL($url) {
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
		
    }
	
	public function SaveImage($url,$fullpath) {
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		$ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/".rand(3,5).".".rand(0,3)." (Windows NT ".rand(3,5).".".rand(0,2)."; rv:2.0.1) Gecko/20100101 Firefox/".rand(3,5).".0.1");
		$rawdata = curl_exec($ch);
		curl_close($ch);
		
		if (file_exists($fullpath)) {
			unlink($fullpath);
		}
		
		$fp = fopen($fullpath,'wb');
		$ok = fwrite($fp, $rawdata);
		
		if ($ok === false) {
            return FALSE;
        }
		
		fclose($fp);
		
		chmod($fullpath, 0664);
		
		return TRUE;
		
	}
 
    private function extra_match_all($regex, $str, $trmme=false) {
	
        if(preg_match_all($regex, $str, $matches) === false) {
		
            return false;
			
        } else {
            
			if ($trmme) {
			
				$mm = str_replace("</a>", "", $matches[5]);
				$mn = preg_replace( '/(<a href="\/character\/ch+([\d]{7})\/">)/ms', '', $mm);
				
			} else {
			
				$mn = $matches[3];
				
			}
			
			return array('nid'=>$matches[1],'name'=>$matches[2],'as'=>$mn);
			
		}
    }
	
	private function match_all($regex, $str, $i = 0)
    {
        if(preg_match_all($regex, $str, $matches) === false)
            return false;
        else
            return $matches[$i];
    }
 
    private function match($regex, $str, $i = 0)
    {
        if(preg_match($regex, $str, $match) == 1)
            return $match[$i];
        else
            return false;
    }
}

?>