<?php

namespace App\Policies;

use App\Models\PharmacyItem;
use App\Models\User;

class PharmacyItemPolicy
{
  /**
   * Determine if the user can view any pharmacy items.
   */
  public function viewAny(User $user): bool
  {
    return in_array($user->role, ['pharmacy', 'admin', 'doctor']);
  }

  /**
   * Determine if the user can view the pharmacy item.
   */
  public function view(User $user, PharmacyItem $pharmacyItem): bool
  {
    return in_array($user->role, ['pharmacy', 'admin', 'doctor']);
  }

  /**
   * Determine if the user can create pharmacy items.
   */
  public function create(User $user): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can update the pharmacy item.
   */
  public function update(User $user, PharmacyItem $pharmacyItem): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can delete the pharmacy item.
   */
  public function delete(User $user, PharmacyItem $pharmacyItem): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can restore the pharmacy item.
   */
  public function restore(User $user, PharmacyItem $pharmacyItem): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can permanently delete the pharmacy item.
   */
  public function forceDelete(User $user, PharmacyItem $pharmacyItem): bool
  {
    return $user->role === 'admin';
  }

  /**
   * Determine if the user can adjust stock.
   */
  public function adjustStock(User $user, PharmacyItem $pharmacyItem): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }
}
