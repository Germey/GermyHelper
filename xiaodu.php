<?php 

	class Xiaodu {

		public function getHeaders() {
			$headers['Cookie'] = 'BDUSS=d0RVJ5czh4TGRuaGo5TWxWNE5OUXNQdm00dTF-fjlBNVpSb1VYNlp1LTR3d0JXQVFBQUFBJCQAAAAAAAAAAAEAAADcrbIDtN7H7LLFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALg22VW4NtlVcW; PSTM=1441037151; BAIDUID=53113E8842695F46438475B8B84F4111:FG=1; BIDUPSID=0157E507AB54354DEB6EC577D9898768; BDRCVFR[feWj1Vr5u3D]=I67x6TjHwwYf0; H_PS_PSSID=1447_16837_12772_12826_14431_17245_17001_17471_17073_15329_17348_12123_17351_16094_17422_17050'; 
			$headers['Host'] = 'sp0.baidu.com';
			$headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36';
			$headerArr = array(); 
			foreach($headers as $n => $v) { 
			    $headerArr[] = $n .':' . $v;  
			}
			return $headerArr;
		}

		public function getResponse($content) {
			$headers = $this->getHeaders();
			$ch = curl_init();
			$url = 'https://sp0.baidu.com/yLsHczq6KgQFm2e88IuM_a/s?sample_name=bear_brain&request_query=' . urlencode($content);
			echo $url;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$out = curl_exec($ch);
			curl_close ($ch);
			print $out;
			print("<pre>");
			print_r(json_decode($out, True));
			print("</pre>");
			return json_decode($out, True);

		}

	}


	//$xiaodu = new Xiaodu();
	//$xiaodu->getResponse("电影");