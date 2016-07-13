<?php
namespace Leno;

abstract class Promise
{
    const PENDING  = 'pending';

    const FULFILLED = 'fulfilled';

    const REJECTED = 'rejected';

    protected $next_context = [];

    protected $status = self::PENDING;

    protected $pid;

    protected $child_pid;

    protected $args = [];


    public function then (callable $onFulfilled, callable $onRejected = null) : Promise
    {
        $this->next_context[] = [
            'fulfilled' => $onFulfilled,
            'rejected' => $onRejected
        ];
        return $this;
    }

    public function execute()
    {
        $this->pid = posix_getpid();
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new PromiseForkException;
        } else if ($pid) {
            $this->child_pid = $pid;
            return;
        }
        pcntl_waitpid($this->pid, $status);
        $this->args[] = call_user_func([$this, '_execute']);
        $this->afterExecute();
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    private function afterExecute()
    {
        foreach ($this->next_context as $context) {
            if ($this->status == self::FULFILLED) {
                return;
            }
            if ($this->status == self::PENDING) {
                $this->args = [call_user_func_array($context['fulfilled'], $this->args)];
            }
            if ($this->status == self::REJECTED) {
                if (!is_callable($context['rejected'])) {
                    return;
                }
                call_user_func($context['rejected']);
                return;
            }
        }
        $this->status = self::FULFILLED;
    }

    abstract protected function _execute();
}
