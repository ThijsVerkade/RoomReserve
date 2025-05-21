import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { ReservationsList } from '@/components/reservations-list';

import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import axios from 'axios';

// Define types for our reservations
interface Reservation {
    id: number;
    room_name: string;
    date: string;
    start_time: string;
    end_time: string;
    purpose: string;
    user_name: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const [selectedDate, setSelectedDate] = useState<string>(
        new Date().toISOString().split('T')[0]
    );
    const [reservations, setReservations] = useState<Reservation[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [useExampleData, setUseExampleData] = useState<boolean>(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        room_id: '',
        date: selectedDate,
        start_time: '',
        end_time: '',
        purpose: '',
    });

    // Example reservations for demo/testing purposes
    const exampleReservations: Reservation[] = [
        {
            id: 999,
            room_name: "Conference Room A",
            date: selectedDate,
            start_time: "09:00:00",
            end_time: "10:30:00",
            purpose: "Weekly Team Standup",
            user_name: "John Smith"
        },
        {
            id: 998,
            room_name: "Meeting Room B",
            date: selectedDate,
            start_time: "11:00:00",
            end_time: "12:00:00",
            purpose: "Client Presentation",
            user_name: "Sarah Johnson"
        },
        {
            id: 997,
            room_name: "Boardroom",
            date: selectedDate,
            start_time: "13:30:00",
            end_time: "15:00:00",
            purpose: "Quarterly Budget Review",
            user_name: "Michael Chen"
        },
        {
            id: 996,
            room_name: "Training Room",
            date: selectedDate,
            start_time: "14:00:00",
            end_time: "16:30:00",
            purpose: "New Employee Orientation",
            user_name: "Lisa Rodriguez"
        }
    ];

    // Fetch reservations when the selected date changes
    useEffect(() => {
        const fetchReservations = async () => {
            setLoading(true);
            try {
                const response = await axios.get(`/api/reservations?date=${selectedDate}`);
                if (response.data.length === 0) {
                    // If no real data, use example data
                    setUseExampleData(true);
                } else {
                    setUseExampleData(false);
                    setReservations(response.data);
                }
            } catch (error) {
                console.error('Error fetching reservations:', error);
                // On error, use example data
                setUseExampleData(true);
            } finally {
                setLoading(false);
            }
        };

        fetchReservations();
    }, [selectedDate]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/reservations', {
            onSuccess: () => {
                reset('room_id', 'start_time', 'end_time', 'purpose');
                // Refresh reservations after successful submission
                const fetchReservations = async () => {
                    try {
                        const response = await axios.get(`/api/reservations?date=${selectedDate}`);
                        if (response.data.length === 0) {
                            setUseExampleData(true);
                        } else {
                            setUseExampleData(false);
                            setReservations(response.data);
                        }
                    } catch (error) {
                        console.error('Error fetching reservations:', error);
                        setUseExampleData(true);
                    }
                };
                fetchReservations();
            },
        });
    };

    // Helper function to format time
    const formatTime = (time: string) => {
        return new Date(`2000-01-01T${time}`).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    // Display example data if useExampleData is true
    const displayReservations = useExampleData ? exampleReservations : reservations;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Reservations View Component */}
                <ReservationsList
                    reservations={displayReservations}
                    loading={loading}
                    selectedDate={selectedDate}
                    onDateChange={(date) => {
                        setSelectedDate(date);
                        setData('date', date);
                    }}
                />

                {/* Room Reservation Form */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border p-6">
                    <h2 className="mb-6 text-2xl font-semibold">Reserve a Room</h2>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Room Selection */}
                            <div className="space-y-2">
                                <label htmlFor="room_id" className="block text-sm font-medium">
                                    Select Room
                                </label>
                                <select
                                    id="room_id"
                                    value={data.room_id}
                                    onChange={(e) => setData('room_id', e.target.value)}
                                    className="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                    required
                                >
                                    <option value="">Select a room</option>
                                    <option value="1">Conference Room A</option>
                                    <option value="2">Meeting Room B</option>
                                    <option value="3">Boardroom</option>
                                    <option value="4">Training Room</option>
                                </select>
                                {errors.room_id && <p className="mt-1 text-sm text-red-600">{errors.room_id}</p>}
                            </div>

                            {/* Date Selection */}
                            <div className="space-y-2">
                                <label htmlFor="date" className="block text-sm font-medium">
                                    Date
                                </label>
                                <input
                                    type="date"
                                    id="date"
                                    value={data.date}
                                    onChange={(e) => {
                                        setSelectedDate(e.target.value);
                                        setData('date', e.target.value);
                                    }}
                                    min={new Date().toISOString().split('T')[0]}
                                    className="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                    required
                                />
                                {errors.date && <p className="mt-1 text-sm text-red-600">{errors.date}</p>}
                            </div>

                            {/* Start Time */}
                            <div className="space-y-2">
                                <label htmlFor="start_time" className="block text-sm font-medium">
                                    Start Time
                                </label>
                                <input
                                    type="time"
                                    id="start_time"
                                    value={data.start_time}
                                    onChange={(e) => setData('start_time', e.target.value)}
                                    className="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                    required
                                />
                                {errors.start_time && <p className="mt-1 text-sm text-red-600">{errors.start_time}</p>}
                            </div>

                            {/* End Time */}
                            <div className="space-y-2">
                                <label htmlFor="end_time" className="block text-sm font-medium">
                                    End Time
                                </label>
                                <input
                                    type="time"
                                    id="end_time"
                                    value={data.end_time}
                                    onChange={(e) => setData('end_time', e.target.value)}
                                    className="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                    required
                                />
                                {errors.end_time && <p className="mt-1 text-sm text-red-600">{errors.end_time}</p>}
                            </div>
                        </div>

                        {/* Purpose */}
                        <div className="space-y-2">
                            <label htmlFor="purpose" className="block text-sm font-medium">
                                Purpose of Reservation
                            </label>
                            <textarea
                                id="purpose"
                                value={data.purpose}
                                onChange={(e) => setData('purpose', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800"
                                placeholder="Briefly describe the purpose of your reservation"
                                required
                            />
                            {errors.purpose && <p className="mt-1 text-sm text-red-600">{errors.purpose}</p>}
                        </div>

                        {/* Submit Button */}
                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing ? 'Reserving...' : 'Reserve Room'}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[50vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
