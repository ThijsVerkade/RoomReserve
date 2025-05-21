import React, { useState } from 'react';

interface Reservation {
    id: number;
    room_name: string;
    date: string;
    start_time: string;
    end_time: string;
    purpose: string;
    user_name: string;
}

interface ReservationsListProps {
    reservations: Reservation[];
    loading: boolean;
    selectedDate: string;
    onDateChange: (date: string) => void;
}

export function ReservationsList({
                                     reservations,
                                     loading,
                                     selectedDate,
                                     onDateChange
                                 }: ReservationsListProps) {
    const [viewMode, setViewMode] = useState<'calendar' | 'list'>('calendar');

    // Helper function to format time
    const formatTime = (time: string) => {
        return new Date(`2000-01-01T${time}`).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    // Group reservations by room
    const reservationsByRoom = reservations.reduce((acc, reservation) => {
        if (!acc[reservation.room_name]) {
            acc[reservation.room_name] = [];
        }
        acc[reservation.room_name].push(reservation);
        return acc;
    }, {} as Record<string, Reservation[]>);

    // Generate time slots for the day (8:00 AM to 6:00 PM)
    const timeSlots = Array.from({ length: 11 }, (_, i) => {
        const hour = i + 8; // Start at 8 AM
        return `${hour.toString().padStart(2, '0')}:00`;
    });

    // Calculate position and width for a reservation
    const getReservationStyle = (startTime: string, endTime: string) => {
        const startHour = parseInt(startTime.split(':')[0]);
        const startMinute = parseInt(startTime.split(':')[1]);
        const endHour = parseInt(endTime.split(':')[0]);
        const endMinute = parseInt(endTime.split(':')[1]);

        // Calculate start position (percentage from left)
        const startPosition = ((startHour - 8) + (startMinute / 60)) / 11 * 100;

        // Calculate width (percentage of total)
        const duration = (endHour - startHour) + ((endMinute - startMinute) / 60);
        const width = (duration / 11) * 100;

        return {
            left: `${startPosition}%`,
            width: `${width}%`
        };
    };

    return (
        <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border p-6">
            <div className="mb-4 flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <h2 className="text-2xl font-semibold">Room Reservations</h2>
                <div className="flex flex-wrap items-center gap-3">
                    <div className="flex items-center space-x-2">
                        <label htmlFor="view-date" className="text-sm font-medium">
                            Date:
                        </label>
                        <input
                            type="date"
                            id="view-date"
                            value={selectedDate}
                            onChange={(e) => onDateChange(e.target.value)}
                            className="rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                        />
                    </div>
                    <div className="flex rounded-md border border-gray-300 dark:border-gray-700">
                        <button
                            onClick={() => setViewMode('calendar')}
                            className={`px-3 py-2 text-sm ${viewMode === 'calendar'
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                                : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300'}`}
                        >
                            Calendar
                        </button>
                        <button
                            onClick={() => setViewMode('list')}
                            className={`px-3 py-2 text-sm ${viewMode === 'list'
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                                : 'bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300'}`}
                        >
                            List
                        </button>
                    </div>
                </div>
            </div>

            {loading ? (
                <div className="flex justify-center py-8">
                    <div className="h-8 w-8 animate-spin rounded-full border-b-2 border-t-2 border-indigo-600"></div>
                </div>
            ) : reservations.length === 0 ? (
                <div className="py-8 text-center text-gray-500">
                    <p>No reservations found for this date.</p>
                </div>
            ) : viewMode === 'calendar' ? (
                <div className="overflow-x-auto">
                    <div className="min-w-[800px]">
                        {/* Header with time slots */}
                        <div className="grid grid-cols-[150px_1fr] mb-2">
                            <div className="font-medium p-2">Room</div>
                            <div className="grid grid-cols-11 border-l">
                                {timeSlots.map((time) => (
                                    <div key={time} className="p-2 text-center text-xs font-medium border-r">
                                        {time}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Room rows with reservations */}
                        {Object.keys(reservationsByRoom).map((roomName) => (
                            <div key={roomName} className="grid grid-cols-[150px_1fr] mb-1 border-t">
                                <div className="p-3 font-medium">
                                    {roomName}
                                </div>
                                <div className="relative h-16 border-l">
                                    {/* Time slot grid lines */}
                                    <div className="grid grid-cols-11 h-full absolute inset-0">
                                        {timeSlots.map((time, index) => (
                                            <div key={`grid-${time}`} className="border-r h-full"></div>
                                        ))}
                                    </div>

                                    {/* Reservations */}
                                    {reservationsByRoom[roomName].map((reservation) => {
                                        const style = getReservationStyle(reservation.start_time, reservation.end_time);
                                        return (
                                            <div
                                                key={reservation.id}
                                                className="absolute top-1 bottom-1 rounded bg-indigo-100 dark:bg-indigo-900 p-2 text-xs overflow-hidden"
                                                style={style}
                                            >
                                                <div className="font-medium truncate">{reservation.purpose}</div>
                                                <div className="text-gray-600 dark:text-gray-400 text-xs">
                                                    {formatTime(reservation.start_time)} - {formatTime(reservation.end_time)}
                                                </div>
                                                <div className="text-gray-600 dark:text-gray-400 text-xs truncate">
                                                    {reservation.user_name}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : (
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Room
                            </th>
                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Time
                            </th>
                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Purpose
                            </th>
                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Reserved By
                            </th>
                        </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        {reservations.map((reservation) => (
                            <tr key={reservation.id}>
                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {reservation.room_name}
                                </td>
                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {formatTime(reservation.start_time)} - {formatTime(reservation.end_time)}
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div className="max-w-xs truncate">{reservation.purpose}</div>
                                </td>
                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {reservation.user_name}
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
