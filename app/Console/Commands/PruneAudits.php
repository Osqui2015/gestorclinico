<?php

namespace App\Console\Commands;

use App\Models\Audit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneAudits extends Command
{
  protected $signature = 'audits:prune {days=90 : Number of days to keep}';

  protected $description = 'Remove audit records older than the given number of days';

  public function handle()
  {
    $days = (int) $this->argument('days');
    $cutoff = Carbon::now()->subDays($days);

    $count = Audit::where('created_at', '<', $cutoff)->delete();

    $this->info("Deleted {$count} audit(s) older than {$days} days.");

    return 0;
  }
}
