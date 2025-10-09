<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misadaku School</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/misadaku.png') }}">
    <meta name="description"
        content="Streamline your school's financial management with School Finance Manager. Track income, manage expenses, create budgets, and generate reports with 99.9% accuracy. Trusted by 500+ schools.">
    <meta name="keywords"
        content="school finance, educational finance management, school budget tracking, expense management, financial reports">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configure Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1e40af',
                        secondary: '#059669',
                        accent: '#dc2626',
                        surface: '#f8fafc',
                        'text-primary': '#0f172a',
                        'text-secondary': '#64748b',
                        'border-custom': '#e2e8f0'
                    }
                }
            }
        }
    </script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #059669 100%);
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }

        .accordion-content.active {
            max-height: 200px;
        }

        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .mobile-menu.active {
            transform: translateX(0);
        }
    </style>
</head>

<body class="font-inter bg-white text-text-primary">
    <!-- Header/Navbar -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">$</span>
                        </div>
                        <span class="ml-2 text-xl font-bold text-text-primary">Misadaku School</span>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    @if (Route::has('login'))
                        <nav class="flex items-center justify-end gap-3 md:gap-4 font-medium">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="inline-block px-5 py-1.5 rounded-md border border-transparent bg-gradient-to-r from-[#f5f5f5] to-[#e8e8e8] text-[#1b1b18] shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:text-white">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-block px-5 py-1.5 rounded-md border border-[#19140035] bg-gradient-to-r from-[#ffffff] to-[#f7f7f7] hover:border-[#1915014a] hover:shadow-md transition-all duration-200 dark:border-[#3E3E3A] dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:hover:border-[#62605b] dark:text-white">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="inline-block px-5 py-1.5 rounded-md border border-[#19140035] bg-gradient-to-r from-[#ffffff] to-[#f7f7f7] hover:border-[#1915014a] hover:shadow-md transition-all duration-200 dark:border-[#3E3E3A] dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:hover:border-[#62605b] dark:text-white">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif

                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-text-secondary hover:text-primary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="mobile-menu fixed inset-y-0 left-0 w-64 bg-white shadow-xl md:hidden z-50">
            <div class="flex items-center justify-between p-4 border-b border-border-custom">
                <span class="text-lg font-bold">Menu</span>
                <button id="close-menu-btn" class="text-text-secondary">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <nav class="mt-8">
                <div class="border-t border-border-custom mt-4 pt-4 px-4 space-y-3">
                    @if (Route::has('login'))
                        <nav class="flex items-center justify-end gap-3 md:gap-4 font-medium">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="inline-block px-5 py-1.5 rounded-md border border-transparent bg-gradient-to-r from-[#f5f5f5] to-[#e8e8e8] text-[#1b1b18] shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:text-white">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-block px-5 py-1.5 rounded-md border border-[#19140035] bg-gradient-to-r from-[#ffffff] to-[#f7f7f7] hover:border-[#1915014a] hover:shadow-md transition-all duration-200 dark:border-[#3E3E3A] dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:hover:border-[#62605b] dark:text-white">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="inline-block px-5 py-1.5 rounded-md border border-[#19140035] bg-gradient-to-r from-[#ffffff] to-[#f7f7f7] hover:border-[#1915014a] hover:shadow-md transition-all duration-200 dark:border-[#3E3E3A] dark:from-[#2c2c2a] dark:to-[#3e3e3a] dark:hover:border-[#62605b] dark:text-white">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif

                </div>
            </nav>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-24 pb-16 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight">
                        Complete Financial Control for Your School
                    </h1>
                    <p class="text-xl text-blue-100 mt-6 leading-relaxed">
                        Transform your school's financial management with our comprehensive platform. Track every
                        dollar, reduce errors by 95%, and save 20+ hours per month with automated reporting and budget
                        oversight.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        @auth
                            <button
                                class="bg-white text-primary px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition duration-200 text-lg">
                                Visit Dashboard
                            </button>
                        @endauth

                        <button
                            class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-primary transition duration-200 text-lg">
                            Request Account
                        </button>
                    </div>
                    <div class="mt-8 flex items-center text-blue-100">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">Easy Monitoring • Reliable and Quick • Independent Financial
                            Management</span>
                    </div>
                </div>
                <div class="lg:ml-8">
                    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl p-6 md:p-8 hover-lift">

                        <!-- Header -->
                        <header class="flex items-center justify-between mb-8">
                            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Financial Dashboard</h1>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                        </header>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <!-- Total Income -->
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h2 class="text-sm font-medium text-gray-500">Total Income</h2>
                                <p class="text-3xl font-bold text-green-500 mt-2">$84,500</p>
                            </div>
                            <!-- Total Expenses -->
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h2 class="text-sm font-medium text-gray-500">Total Expenses</h2>
                                <p class="text-3xl font-bold text-red-500 mt-2">$42,300</p>
                            </div>
                            <!-- Net Balance -->
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h2 class="text-sm font-medium text-gray-500">Net Balance</h2>
                                <p class="text-3xl font-bold text-blue-500 mt-2">$42,200</p>
                            </div>
                        </div>

                        <!-- Bar Chart Placeholder -->
                        <div class="bg-gray-50 p-6 rounded-xl mb-8">
                            <div class="flex items-end justify-between h-48">
                                <div class="w-8 md:w-10 bg-blue-400 rounded-t-lg" style="height: 60%;"></div>
                                <div class="w-8 md:w-10 bg-green-400 rounded-t-lg" style="height: 85%;"></div>
                                <div class="w-8 md:w-10 bg-blue-400 rounded-t-lg" style="height: 40%;"></div>
                                <div class="w-8 md:w-10 bg-green-400 rounded-t-lg" style="height: 75%;"></div>
                                <div class="w-8 md:w-10 bg-blue-400 rounded-t-lg" style="height: 90%;"></div>
                                <div class="w-8 md:w-10 bg-green-400 rounded-t-lg" style="height: 65%;"></div>
                                <!-- Added more bars for better visual representation on wider screens -->
                                <div class="hidden sm:block w-8 md:w-10 bg-blue-400 rounded-t-lg"
                                    style="height: 50%;"></div>
                                <div class="hidden md:block w-8 md:w-10 bg-green-400 rounded-t-lg"
                                    style="height: 80%;"></div>
                            </div>
                        </div>


                        <!-- Recent Transactions -->
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Transactions</h2>
                            <div class="space-y-4">
                                <!-- Transaction Item 1 -->
                                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                    <p class="text-gray-600">Tuition Fees - Grade 10</p>
                                    <p class="font-medium text-green-500">+$12,500</p>
                                </div>
                                <!-- Transaction Item 2 -->
                                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                    <p class="text-gray-600">Facility Maintenance</p>
                                    <p class="font-medium text-red-500">-$3,200</p>
                                </div>
                                <!-- Transaction Item 3 -->
                                <div class="flex items-center justify-between py-3">
                                    <p class="text-gray-600">Sports Equipment</p>
                                    <p class="font-medium text-red-500">-$1,800</p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-text-primary mb-4">Everything You Need to Manage School Finances
                </h2>
                <p class="text-xl text-text-secondary max-w-3xl mx-auto">
                    From income tracking to comprehensive reporting, our platform provides all the tools needed for
                    transparent, accurate financial management.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <div class="bg-white p-8 rounded-xl shadow-lg hover-lift">
                    <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-text-primary mb-3">Expense Management</h3>
                    <p class="text-text-secondary">Monitor operational costs, payroll, supplies, and capital
                        expenditures with approval workflows and spending limits.</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover-lift">
                    <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-secondary" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-text-primary mb-3">Budget Planning</h3>
                    <p class="text-text-secondary">Create detailed budgets by department, project, or fiscal year with
                        variance analysis and forecasting tools.</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover-lift">
                    <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-text-primary mb-3">Financial Reports</h3>
                    <p class="text-text-secondary">Generate comprehensive reports for board meetings, audits, and
                        regulatory compliance with one-click automation.</p>
                </div>

                <div class="bg-white p-8 rounded-xl shadow-lg hover-lift">
                    <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-secondary" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-1a6 6 0 00-1.78-4.125" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-text-primary mb-3">Multi-User Access</h3>
                    <p class="text-text-secondary">Secure role-based access for administrators, finance staff, and
                        board members with audit trails and permissions.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- <!-- Statistics Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-text-primary mb-4">Trusted by Schools Nationwide</h2>
                <p class="text-lg text-text-secondary">Our platform delivers measurable results for educational institutions</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary mb-2">500+</div>
                    <div class="text-text-secondary">Schools Served</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-secondary mb-2">99.9%</div>
                    <div class="text-text-secondary">Financial Accuracy</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-accent mb-2">$2M+</div>
                    <div class="text-text-secondary">Money Saved</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary mb-2">4.9/5</div>
                    <div class="text-text-secondary">User Satisfaction</div>
                </div>
            </div>
        </div>
    </section> --}}

    <!-- Footer -->
    <footer class="bg-text-primary text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">$</span>
                        </div>
                        <span class="ml-2 text-xl font-bold">Misadaku School</span>
                    </div>
                    <p class="text-gray-400 mb-4">Complete financial management solutions for educational institutions.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.35-1.832.656-2.828.775.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 2.14 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2025 Misadaku School. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('active');
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        closeMenuBtn.addEventListener('click', toggleMobileMenu);

        // Close mobile menu when clicking on navigation links
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });

        // FAQ accordion functionality
        const faqToggles = document.querySelectorAll('.faq-toggle');

        faqToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const target = document.getElementById(toggle.getAttribute('data-target'));
                const icon = toggle.querySelector('svg');

                // Close all other accordions
                faqToggles.forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        const otherTarget = document.getElementById(otherToggle.getAttribute(
                            'data-target'));
                        const otherIcon = otherToggle.querySelector('svg');
                        otherTarget.classList.remove('active');
                        otherIcon.style.transform = 'rotate(0deg)';
                    }
                });

                // Toggle current accordion
                target.classList.toggle('active');
                if (target.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        // Contact form validation and submission
        const contactForm = document.getElementById('contact-form');

        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Get form data
            const formData = new FormData(contactForm);
            const firstName = formData.get('first-name');
            const lastName = formData.get('last-name');
            const email = formData.get('email');
            const schoolName = formData.get('school-name');
            const message = formData.get('message');

            // Basic validation
            if (!firstName || !lastName || !email || !schoolName) {
                alert('Please fill in all required fields.');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }

            // Simulate form submission
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;

            setTimeout(() => {
                alert('Thank you for your message! We\'ll get back to you within 24 hours.');
                contactForm.reset();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('nav');
            if (window.scrollY > 100) {
                navbar.classList.add('shadow-xl');
            } else {
                navbar.classList.remove('shadow-xl');
            }
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all sections
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>

</html>
