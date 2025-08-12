@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Change History</h1>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Book</label>
                <select name="book_id" class="border border-gray-300 rounded-md px-3 py-2">
                    <option value="">All Books</option>
                    @foreach($books as $book)
                        <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                            {{ $book->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Filter
            </button>
            <a href="{{ route('audit-logs.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Clear
            </a>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @if($log->book)
                                <div class="font-medium">{{ $log->book->title }}</div>
                                <div class="text-gray-500">ISBN: {{ $log->book->isbn }}</div>
                            @else
                                <span class="text-gray-500">Book not found</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @foreach($log->getReadableChanges() as $change)
                                <div class="mb-1">
                                    <span class="font-medium">{{ $change['field'] }}:</span>
                                    <span class="text-red-600">{{ $change['old'] }}</span>
                                    →
                                    <span class="text-green-600">{{ $change['new'] }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $log->user_identifier }}</div>
                            <div class="text-xs text-gray-400">{{ $log->user_source }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No changes recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $auditLogs->links() }}
    </div>
</div>
@endsection