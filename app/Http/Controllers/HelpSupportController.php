<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\FeatureRequest;
use App\Models\HelpFeedback;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HelpSupportController extends Controller
{
    public function index()
    {
        $faqItems = [
            ['question' => 'How do I create an account?', 'answer' => 'To create an account, click Sign Up from the welcome page, enter your name and email, then follow the prompts to complete registration. You can also sign in with Google for faster access.'],
            ['question' => 'How does Google Sign-In work?', 'answer' => 'Google Sign-In lets you authenticate using your Google account. When you choose this option, SHEELEARN securely verifies your Google email without requiring a separate password.'],
            ['question' => 'Why didn\'t I receive my verification email?', 'answer' => 'Check your spam folder and ensure your email address is correct. If you still do not receive it, request the verification email again or contact support.'],
            ['question' => 'How can I reset my password?', 'answer' => 'Open the password reset flow from the login page, enter your registered email, and follow the email link to choose a new password.'],
            ['question' => 'How do I upload PDF files?', 'answer' => 'Use the Documents page to upload PDFs. Supported formats include PDF, DOCX, TXT, and Markdown. Files are processed securely for study and summarization.'],
            ['question' => 'Can AI summarize PowerPoint presentations?', 'answer' => 'Yes. Upload your presentation file to the Documents area, and the AI will analyze the slides and produce a concise summary.'],
            ['question' => 'How many quizzes can I generate?', 'answer' => 'You can generate unlimited quizzes based on your study materials while within your plan limits. Performance scales with content size and quiz complexity.'],
            ['question' => 'Can I use SHEELEARN on my phone?', 'answer' => 'Absolutely. SHEELEARN is responsive and works on mobile browsers, letting you study, review flashcards, and access support wherever you are.'],
            ['question' => 'How do I contact support?', 'answer' => 'Use the contact form on this page or send an email directly to dasinagee2@gmail.com. For urgent issues, choose Live Chat during available hours.'],
            ['question' => 'How is my data protected?', 'answer' => 'SHEELEARN uses secure storage and encryption for sensitive data. Your files and account information are kept private and handled in accordance with our security practices.'],
        ];

        return view('help', [
            'faqItems' => $faqItems,
            'quickTags' => ['AI Chat','Flashcards','Quiz Generator','My Documents','Analytics','Account','Settings','Password','Google Login'],
        ]);
    }

    public function search(Request $request)
    {
        $query = strtolower($request->validate(['query' => 'nullable|string|max:255'])['query'] ?? '');
        $results = [];

        $articles = [
            ['title' => 'Getting Started with SHEELEARN', 'category' => 'Onboarding', 'content' => 'Learn how to create an account, connect Google Sign-In, and start your first study session.'],
            ['title' => 'AI Chat Guide', 'category' => 'AI Chat', 'content' => 'Tips for using AI Tutor, asking better prompts, and reviewing responses effectively.'],
            ['title' => 'Flashcards Best Practices', 'category' => 'Flashcards', 'content' => 'Create, edit, and share flashcards to reinforce key concepts.'],
            ['title' => 'Quiz Generator Overview', 'category' => 'Quizzes', 'content' => 'Generate practice quizzes, select difficulty, and track your learning progress.'],
            ['title' => 'Uploading Documents', 'category' => 'Documents', 'content' => 'Supported formats, file size limits, and how to manage uploaded study materials.'],
            ['title' => 'Analytics & Progress Tracking', 'category' => 'Analytics', 'content' => 'Track your study stats, identify trends, and improve your learning habits.'],
            ['title' => 'Account Settings', 'category' => 'Account', 'content' => 'Update your profile, change your password, and manage email verification.'],
            ['title' => 'Troubleshooting Common Issues', 'category' => 'Technical Issues', 'content' => 'Resolve loading errors, browser compatibility issues, and slow performance.'],
        ];

        if ($query !== '') {
            foreach ($articles as $article) {
                if (str_contains(strtolower($article['title']), $query) || str_contains(strtolower($article['content']), $query) || str_contains(strtolower($article['category']), $query)) {
                    $results[] = $article;
                }
            }
        }

        return response()->json(['results' => $results]);
    }

    public function submitTicket(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:150',
            'category' => 'required|string|max:80',
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,txt|max:10240',
        ]);

        $user = Auth::user();
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support-attachments', 'public');
        }

        $referenceId = SupportTicket::max('id') + 1;
        $referenceId = sprintf('SUP-2026-%06d', $referenceId);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'message' => $data['message'],
            'attachment_path' => $attachmentPath,
            'reference_id' => $referenceId,
        ]);

        try {
            Mail::raw("New support ticket received: {$ticket->reference_id}\nSubject: {$ticket->subject}\nCategory: {$ticket->category}\nUser: {$user->name} <{$user->email}>\nMessage:\n{$ticket->message}", function ($message) use ($ticket) {
                $message->to('dasinagee2@gmail.com')
                    ->subject('New SHEELEARN Support Ticket: ' . $ticket->reference_id);
            });
        } catch (\Throwable $e) {
            // continue; do not block the form
        }

        return response()->json(['message' => 'Support ticket created successfully.', 'reference_id' => $ticket->reference_id]);
    }

    public function submitBug(Request $request)
    {
        $data = $request->validate([
            'page' => 'required|string|max:120',
            'description' => 'required|string|max:1500',
            'reproduction_steps' => 'required|string|max:2000',
            'severity' => 'required|string|in:Low,Medium,High,Critical',
            'screenshot' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $user = Auth::user();
        $screenshotPath = null;

        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('bug-screenshots', 'public');
        }

        BugReport::create([
            'user_id' => $user->id,
            'page' => $data['page'],
            'description' => $data['description'],
            'reproduction_steps' => $data['reproduction_steps'],
            'severity' => $data['severity'],
            'screenshot_path' => $screenshotPath,
        ]);

        return response()->json(['message' => 'Bug report submitted successfully.']);
    }

    public function submitFeature(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:140',
            'description' => 'required|string|max:1800',
            'expected_benefit' => 'required|string|max:1500',
        ]);

        $user = Auth::user();

        FeatureRequest::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'expected_benefit' => $data['expected_benefit'],
        ]);

        return response()->json(['message' => 'Feature request submitted successfully.']);
    }

    public function submitFeedback(Request $request)
    {
        $data = $request->validate([
            'rating' => 'required|in:yes,no',
            'comment' => 'nullable|string|max:1200',
        ]);

        $user = Auth::user();

        HelpFeedback::create([
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json(['message' => 'Feedback submitted successfully.']);
    }

    public function systemStatus()
    {
        $status = [
            'ai_services' => ['label' => 'AI Services', 'status' => 'online'],
            'authentication' => ['label' => 'Authentication', 'status' => 'operational'],
            'email_services' => ['label' => 'Email Services', 'status' => 'operational'],
            'database' => ['label' => 'Database', 'status' => 'healthy'],
            'updated_at' => now()->toDateTimeString(),
        ];

        return response()->json($status);
    }
}
