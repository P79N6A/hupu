<?php
/** 粉丝管理 */

class Fans extends HomeBase
    /**
     * 我的粉丝
     */
    public function mineFans()
        return $this->fetch();
    }
    /**
     * 直接粉丝
     */
    public function directFans()
        return $this->fetch();
    }
}