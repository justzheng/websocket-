<?php
//	error_reporting(E_ALL);
//	set_time_limit(0);
//	ob_start();
//	ob_implicit_flush();
//
//	$socket=new socket('127.0.0.1','8000');
//	$socket->run();
//
//	class socket{
//		protected $hand;
//		public $soc;
//		public $socs;
//		public function  __construct($address,$port)
//		{
//			//建立套接字
//			$this->soc=$this->createSocket($address,$port);
//			$this->socs=array($this->soc);
//
//		}
//		//建立套接字
//		public function createSocket($address,$port)
//		{
//			//创建一个套接字
//			$socket= socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//			//设置套接字选项
//	        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
//	        //绑定IP地址和端口
//	        socket_bind($socket,$address,$port);
//	        //监听套接字
//	        socket_listen($socket);
//	        return $socket;
//		}
//
//		public function run(){
//			//挂起进程
//			while(true){
//				$arr=$this->socs;
//				$write=$except=NULL;
//				//接收套接字数字 监听他们的状态
//				socket_select($arr,$write,$except, NULL);
//				//遍历套接字数组
//				foreach($arr as $k=>$v){
//					//如果是新建立的套接字返回一个有效的 套接字资源
//					if($this->soc == $v){
//						$client=socket_accept($this->soc);
//						if($client <0){
//							echo "socket_accept() failed";
//						}else{
//							// array_push($this->socs,$client);
//							// unset($this[]);
//							//将有效的套接字资源放到套接字数组
//							$this->socs[]=$client;
//						}
//					}else{
//						//从已连接的socket接收数据  返回的是从socket中接收的字节数
//						$byte=socket_recv($v, $buff,20480, 0);
//						//如果接收的字节是0
//						if($byte<7)
//							continue;
//						//判断有没有握手没有握手则进行握手,如果握手了 则进行处理
//						if(!$this->hand[(int)$client]){
//							//进行握手操作
//							$this->hands($client,$buff,$v);
//						}else{
//							//处理数据操作
//							$mess=$this->decodeData($buff);
//							$s = $mess;
//				           	//发送数据
//							$this->send($mess,$v);
//						}
//					}
//				}
//			}
//		}
//		//进行握手
//		public function hands($client,$buff,$v)
//		{
//			//提取websocket传的key并进行加密  （这是固定的握手机制获取Sec-WebSocket-Key:里面的key）
//			$buf  = substr($buff,strpos($buff,'Sec-WebSocket-Key:')+18);
//			//去除换行空格字符
//	        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
//	     	//固定的加密算法
//	        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
//			$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
//	        $new_message .= "Upgrade: websocket\r\n";
//	        $new_message .= "Sec-WebSocket-Version: 13\r\n";
//	        $new_message .= "Connection: Upgrade\r\n";
//	        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
//	        //将套接字写入缓冲区
//	        socket_write($v,$new_message,strlen($new_message));
//	        // socket_write(socket,$upgrade.chr(0), strlen($upgrade.chr(0)));
//	        //标记此套接字握手成功
//	        $this->hand[(int)$client]=true;
//		}
//
//		//解析数据
//		public  function  decodeData($buff)
//		{
//			//$buff  解析数据帧
//			$mask = array();
//	        $data = '';
//	        $msg = unpack('H*',$buff);  //用unpack函数从二进制将数据解码
//	        $head = substr($msg[1],0,2);
//	        if (hexdec($head{1}) === 8) {
//	            $data = false;
//	        }else if (hexdec($head{1}) === 1){
//	            $mask[] = hexdec(substr($msg[1],4,2));
//	            $mask[] = hexdec(substr($msg[1],6,2));
//	            $mask[] = hexdec(substr($msg[1],8,2));
//	            $mask[] = hexdec(substr($msg[1],10,2));
//	           	//遇到的问题  刚连接的时候就发送数据  显示 state connecting
//	            $s = 12;
//	            $e = strlen($msg[1])-2;
//	            $n = 0;
//	            for ($i=$s; $i<= $e; $i+= 2) {
//	                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));
//	                $n++;
//	            }
//	            //发送数据到客户端
//	           	//如果长度大于125 将数据分块
//	           	$block=str_split($data,125);
//	           	$mess=array(
//	           		'mess'=>$block[0],
//	           		);
//				return $mess;
//	        }
//		}
//
//		//发送数据
//		public function send($mess,$v)
//		{
//			//遍历套接字数组 成功握手的  进行数据群发
//			foreach ($this->socs as $keys => $values) {
//				//用系统分配的套接字资源id作为用户昵称
//           		$mess['name']="Tourist's socket:{$v}";
////           		file_put_contents('E:\1.txt',1123,FILE_APPEND);
//           		$str=json_encode($mess);
////                $str = 'qwe';
//           		$writes ="\x81".chr(strlen($str)).$str;
//                //$writes ="12";
//                // ob_flush();
//           		// flush();
//           		// sleep(3);
//           		if($this->hand[(int)$values])
//       				socket_write($values,$writes,strlen($writes));
//                file_put_contents('E:\1.txt','in-out'.$keys.'\r',FILE_APPEND);
//           	}
//		}
//
//	}
class WebSocketServer{
    private $sockets;//所有socket连接池包括服务端socket
    private $users;//所有连接用户
    private $server;//服务端 socket

    public function __construct($ip,$port){
        $this->server=socket_create(AF_INET,SOCK_STREAM,0);
        $this->sockets=array($this->server);
        $this->users=array();
        socket_bind($this->server,$ip,$port);
        socket_listen($this->server);
        echo "[*]Listening:".$ip.":".$port."\n";
    }

    public function run(){
        $write=NULL;
        $except=NULL;
        while (true){
            $active_sockets=$this->sockets;
//            var_dump($this->sockets);
//            var_dump(1111);
            $re = socket_select($active_sockets,$write,$except,NULL);
//            var_dump($active_sockets);
//            var_dump(2222);
//            var_dump(11);
            //这个函数很重要
            //前三个参数时传入的是数组的引用,会依次从传入的数组中选择出可读的,可写的,异常的socket,我们只需要选择出可读的socket
            //最后一个参数tv_sec很重要
            //第一，若将NULL以形参传入，即不传入时间结构，就是将select置于阻塞状态，一定等到监视文件描述符集合(socket数组)中某个文件描
            //述符发生变化为止；
            //第二，若将时间值设为0秒0毫秒，就变成一个纯粹的非阻塞函数，不管文件描述符 是否有变化，都立刻返回继续执行，文件无
            //变化返回0，有变化返回一个正值；
            //第三，timeout的值大于0，这就是等待的超时时间，即 select在timeout时间内阻塞，超时时间之内有事件到来就返回了，
            //否则在超时后不管怎样一定返回，返回值同上述。
            foreach ($active_sockets as $socket){
                //var_dump(3333);
                if ($socket==$this->server){
//                    var_dump(123);
                    //服务端 socket可读说明有新用户连接
                    $user=socket_accept($this->server);
//                    var_dump($active_sockets);
//                    var_dump(12);
//                    var_dump($user);
                    $key=uniqid();
                    $this->sockets[]=$user;
                    $this->users[$key]=array(
                        'socket'=>$user,
                        'handshake'=>false //是否完成websocket握手
                    );
                }else{
                    //var_dump($this->users);
                    //用户socket可读
                    $buffer='';
                    $bytes=socket_recv($socket,$buffer,1024,0);
                    $key=$this->find_user_by_socket($socket); //通过socket在users数组中找出user
                    if ($bytes==0){
                        //没有数据 关闭连接
                        $this->disconnect($socket);
                    }else{
                        //没有握手就先握手
                        if (!$this->users[$key]['handshake']){
                            $this->handshake($key,$buffer);
                        }else{
                            //握手后
                            //解析消息 websocket协议有自己的消息格式
                            //解码 编码过程固定的
                            $msg=$this->msg_decode($buffer);
                            //echo $msg;
                            //编码后发送回去
                            $res_msg=$this->msg_encode($msg);
                            $this->send($res_msg);
                            //socket_write($socket,$res_msg,strlen($res_msg));

                        }
                    }
                }
            }
        }
    }

    //解除连接
    private function disconnect($socket){
        $key=$this->find_user_by_socket($socket);
        unset($this->users[$key]);
        foreach ($this->sockets as $k=>$v){
            if ($v==$socket)
                unset($this->sockets[$k]);
        }
        socket_shutdown($socket);
        socket_close($socket);
    }

    //通过socket在users数组中找出user
    private function find_user_by_socket($socket){
        foreach ($this->users as $key=>$user){
            if ($user['socket']==$socket){
                return $key;
            }
        }
        return -1;
    }

    private function handshake($k,$buffer){
        //截取Sec-WebSocket-Key的值并加密
        $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));

        //按照协议组合信息进行返回
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $new_message .= "Upgrade: websocket\r\n";
        $new_message .= "Sec-WebSocket-Version: 13\r\n";
        $new_message .= "Connection: Upgrade\r\n";
        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
        socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));

        //对已经握手的client做标志
        $this->users[$k]['handshake']=true;
        return true;
    }


    //编码 把消息打包成websocket协议支持的格式
    private function msg_encode( $buffer ){
        $len = strlen($buffer);
        if ($len <= 125) {
            return "\x81" . chr($len) . $buffer;
        } else if ($len <= 65535) {
            return "\x81" . chr(126) . pack("n", $len) . $buffer;
        } else {
            return "\x81" . char(127) . pack("xxxxN", $len) . $buffer;
        }
    }

    //解码 解析websocket数据帧
    private function msg_decode( $buffer )
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    //发送数据
		public function send($mess)
		{
			//遍历套接字数组 成功握手的  进行数据群发
			foreach ($this->sockets as $keys => $values) {
                if($this->server == $values){
                    continue;
                }
				//用系统分配的套接字资源id作为用户昵称
       				socket_write($values,$mess,strlen($mess));
           	}
		}
}

$ws=new WebSocketServer('127.0.0.1',8888);
$ws->run();