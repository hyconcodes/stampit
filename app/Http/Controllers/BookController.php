<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function getBooks()
    {
        // Logic to get all books
        $books = Book::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Get all books',
            'books' => $books,
        ]);
    }

    public function addBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string|max:255',
            'image' => 'required|file|image|max:5048',
            "rating" => 'required|integer|min:1|max:5',
        ]);
        $book = Book::create([
            'title' => $request->title,
            'caption' => $request->caption,
            'user_id' => auth()->user()->id,
            'rating' => $request->rating,
            'image' => $request->file('image')->store('images', 'public'),
        ]);
        return response()->json([
            'message' => 'Book added successfully',
            'book' => $book,
        ]);
    }

    public function updateBook($id, Request $request)
    {
        // Logic to update a book by ID
        return response()->json(['message' => 'Update book with ID: ' . $id]);
    }

    public function deleteBook($id)
    {
        // Logic to delete a book by ID
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        Storage::disk('public')->delete($book->image);
        $book->delete();
        return response()->json(['message' => 'Delete book with ID: ' . $id]);
    }

    public function getBook($id)
    {
        // Logic to get a single book by ID
        return response()->json(['message' => 'Get book with ID: ' . $id]);
    }
}
