<?php

namespace App\Policies;

use App\Models\PharmacyRequest;
use App\Models\User;

class PharmacyRequestPolicy
{
  /**
   * Determine if the user can view any pharmacy requests.
   */
  public function viewAny(User $user): bool
  {
    return in_array($user->role, ['pharmacy', 'admin', 'doctor']);
  }

  /**
   * Determine if the user can view the pharmacy request.
   */
  public function view(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    // Pharmacy and admin can view all
    if (in_array($user->role, ['pharmacy', 'admin'])) {
      return true;
    }

    // Doctors can only view their own requests
    if ($user->role === 'doctor') {
      return $pharmacyRequest->requested_by === $user->id;
    }

    return false;
  }

  /**
   * Determine if the user can create pharmacy requests.
   */
  public function create(User $user): bool
  {
    return in_array($user->role, ['doctor', 'admin']);
  }

  /**
   * Determine if the user can update the pharmacy request.
   */
  public function update(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    // Pharmacy can update any request
    if (in_array($user->role, ['pharmacy', 'admin'])) {
      return true;
    }

    // Doctors can only update their own pending requests
    if ($user->role === 'doctor' && $pharmacyRequest->requested_by === $user->id) {
      return $pharmacyRequest->status === 'pending';
    }

    return false;
  }

  /**
   * Determine if the user can delete the pharmacy request.
   */
  public function delete(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    return $user->role === 'admin';
  }

  /**
   * Determine if the user can process the pharmacy request.
   */
  public function process(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can deliver items from the pharmacy request.
   */
  public function deliver(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    return in_array($user->role, ['pharmacy', 'admin']);
  }

  /**
   * Determine if the user can cancel the pharmacy request.
   */
  public function cancel(User $user, PharmacyRequest $pharmacyRequest): bool
  {
    // Pharmacy and admin can cancel any request
    if (in_array($user->role, ['pharmacy', 'admin'])) {
      return true;
    }

    // Doctors can cancel their own pending requests
    if ($user->role === 'doctor' && $pharmacyRequest->requested_by === $user->id) {
      return $pharmacyRequest->status === 'pending';
    }

    return false;
  }
}
