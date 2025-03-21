<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserBalance implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(
        public User $user,
        public float $amount
    )
    {}

    /**
     * 确认用户ID的唯一
     * @return string
     */
    public function uniqueId(): string
    {
        return 'user_balance:' . $this->user->id;
    }

    /**
     * 唯一锁保持60秒
     * @return int
     */
    public function uniqueFor() : int
    {
        return 60;
    }

    /**
     * 操作更新余额
     * @return void
     */
    public function handle(): void
    {
//        // 先创建锁,使用Laravel的原子锁确保处理时的互斥性
//        $lock = Cache::lock('user_balance:'. $this->user->id, 10);
//        // 使用事务进行操作
//        try {
//            $lock->block(10, function () {
//                DB::transaction(function () {
//                    // 刷新数据 确保不会在脏读情况下操作
//                    $this->user->refresh();
//                    // 进行增减更新操作 自动保存
//                    $this->user->increment('balance', $this->amount);
//                });
//            });
//        } catch (LockTimeoutException $exception) {
//            $this->release(10);
//        }
        // 直接使用事务处理更新余额
        DB::transaction(function () {
            // 使用悲观锁保证数据一致性
            $user = User::where('id', $this->user->id)
                    ->lockForUpdate()
                    ->first();

            $user->balance += $this->amount;
            $user->save();
        });
    }

    /**
     * 操作失败打印日志
     * @param \Throwable $exception
     * @return void
     */
    public function fail(\Throwable $exception) : void
    {
        Log::error("余额更新失败: { $this->user->id }:" . $exception->getMessage() );
    }


}
