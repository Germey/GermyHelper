<?php 

	class Weixin {

		private $xiaodu;
		private $mysql;

		public function __construct() {
			$this->xiaodu = new Xiaodu();
			$this->mysql = new Mysql();
		}

		//回复文本消息，传入文本内容
		private function responseText($postObj = null, $content = '') {
			if ($postObj) {
				//回复文本消息
				$toUser = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time = time();
				$msgType = 'text';
				$content = $this->updateText($content);
				$template = '<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>';
				$info = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
				echo $info;
				$this->mysql->insertData($toUser, date("Y-m-d"), $postObj->Content, $content, "text");
			}
		}

		//回复图文消息，传入图文信息
		private function responseTextPic($postObj = null, $contentArray = null) {
			if ($postObj) {
				//回复文本消息
				$toUser = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time = time();
				$msgType = 'news';
				$template = '<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<ArticleCount>'.count($contentArray).'</ArticleCount>
							<Articles>';
				foreach ($contentArray as $key => $value) {
					$template .= '<item>
							<Title><![CDATA['.$value['title'].']]></Title> 
							<Description><![CDATA['.$value['description'].']]></Description>
							<PicUrl><![CDATA['.$value['picurl'].']]></PicUrl>
							<Url><![CDATA['.$value['url'].']]></Url>
							</item>';
				}
				$template .= '</Articles>
							</xml>';
				$info = sprintf($template, $toUser, $fromUser, $time, $msgType);
				//$file = fopen("a.txt",'w'); fwrite($file,$info); fclose($file);
				echo $info;
				$this->mysql->insertData($toUser, date("Y-m-d"), $postObj->Content, $info, "news");
			}
		}


		//获得请求的内容
		public function getPostObj() {
			$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
			$postObj = simplexml_load_string($postArr);
			return $postObj;
		}

		public function updateText($text) {
			$text = str_replace('小度','小觅' ,$text);
			return $text;
		}

		//组建图文的一个item
		private function buildItemArray($result_content) {
			$arr = array();
			if (!$result_content['title']) {
				$result_content['title'] = $result_content['subtitle'];
			}
			if (!$result_content['url']) {
				$result_content['url'] = 'https://www.baidu.com/s?wd=' . $result_content['title'];
			}
			$arr['title'] = $result_content['title'];
			$arr['description'] = $result_content['subtitle'];
			$arr['picurl'] = $result_content['img'];
			$arr['url'] = $result_content['url'];
			return $arr;
		}

		//组建多图文的一个数组
		private function buildItemsArray($result_list) {
			$itemsArray = array();
			foreach ($result_list as $key => $value) {
				$result_content = $value['result_content'];
				if ($result_content['subtitle']) {
					//构建多图文的数组
					$arr = $this->buildItemArray($result_content);
					array_push($itemsArray, $arr);
				}
			}
			return $itemsArray;
		}

		//返回的信息在data里面，是JSON格式
		private function buildListArray($data) {
			$data = json_decode($data, True);
			$itemsArray = array();
			foreach ($data as $key => $value) {
				//构建多图文的数组
				$arr['title'] = $value['title'];
				$arr['url'] = 'https://www.baidu.com/s?wd=' . $value['title'];
				$arr['description'] = '';
				$arr['picurl'] = '';
				array_push($itemsArray, $arr);
			}
			return $itemsArray;
		}

		//如果第一个回复结果就可以解决问题
		private function canSolveByFirst($result_list) {
			//获取返回结果的大小
			$size = count($result_list);
			//如果数组大小为1
			if ($size == 1) {
				return true;
			//如果数组大小大于1
			} else if ($size > 1) {
				//如果第一条含有内容是JSON，而不是数组
				if (!is_array($result_list[0]['result_content'])) {
					return true;
				} else {
					return false;
				}
			}
			
		}

		//用第一个结果解决问题
		private function solveByFirst($postObj = null, $result_list = null) {
			//存在第一个元素
			if ($result_list[0]) {
				$result_content = $result_list[0]['result_content'];
				//如果是数组，说明是图文消息或者直接的回答
				if (is_array($result_content)) {
					//如果存在直接的回答，那么直接输出回答
					if ($answer = $result_content['answer']) {
						$this->responseText($postObj, $answer);
					//否则推送单图文消息，构建的数组大小为1
					} else {
						$itemsArray = $this->buildItemsArray($result_list);
						$file = fopen("a.txt",'w'); fwrite($file, json_encode($itemsArray)); fclose($file);
						$this->responseTextPic($postObj, $itemsArray);
					}
				//不是数组，那么解析JSON，分析答案
				} else {
					$result_content = json_decode($result_content, True);
					if ($data = $result_content['data']) {
						//如果存在data数据，说明返回的内容附加在了JSON里，解析之
						$itemsArray = $this->buildListArray($data);
						$this->responseTextPic($postObj, $itemsArray);
					} else if ($answer = $result_content['answer']){
						$this->responseText($postObj, $answer);
					} else {
						$text = "呀，小觅没有听懂你在说什么";
						$this->responseText($postObj, $text);
					}
				}
			}
		}

		//通过多个返回信息来解决问题
		private function solveByMore($postObj = null, $result_list = null) {
			$itemsArray = $this->buildItemsArray($result_list);
			$this->responseTextPic($postObj, $itemsArray);
		}

		//回复
		public function response() {
			//说明此处是第一次验证
			if ($echostr = $_GET['echostr']) {
				echo $echostr;
			//此处代表接收到了消息
			} else {
				//获取POST的数据
				$postObj = $this->getPostObj();
				//接收到了事件推送
				if ($postObj->MsgType == 'event') {
					//订阅该公众号
					if ($postObj->Event == 'subscribe') {
						$subscribeText = '欢迎关注静觅小助手';
						$this->responseText($postObj, $subscribeText);
					}
				//接收到了文本消息
				} else if ($postObj->MsgType == 'text') {
					$text = $postObj->Content;
					$responseArray = $this->xiaodu->getResponse($text);
					$result_list = $responseArray['result_list'];
					if ($result_list) {
						//如果小度返回的第一个结果就可以解决问题
						if ($this->canSolveByFirst($result_list)) {
							$this->solveByFirst($postObj, $result_list);
						} else {
							$this->solveByMore($postObj, $result_list);
						}
						
					}
				}
			}
		}
	}