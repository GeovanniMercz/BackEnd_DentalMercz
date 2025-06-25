<?php

namespace App\Repositories\Appointment;

use App\Models\Appointment;

class AppointmentRepository
{
    public function all()
    {
        return Appointment::with('user')->latest()->get();
    }

    public function create(array $data)
    {
        return Appointment::create($data);
    }

    public function find($id)
    {
        return Appointment::with('user')->findOrFail($id);
    }

    public function update(Appointment $appointment, array $data)
    {
        $appointment->update($data);
        return $appointment;
    }

    public function delete(Appointment $appointment)
    {
        return $appointment->delete();
    }
}
