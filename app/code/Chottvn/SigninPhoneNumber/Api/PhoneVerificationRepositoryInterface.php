<?php 
/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\SigninPhoneNumber\Api;

/**
 * Interface PhoneVerificationRepositoryInterface
 *
 * @package Chottvn\SigninPhoneNumber\Api
 */
interface PhoneVerificationRepositoryInterface {

	/**
	 * POST for Post api
	 * @param string $customerId
	 * @return string
	 */
	
	public function sendOTP($customerId);

	/**
	 * POST for Post api
	 * @param string $customerId
	 * @return string
	 */
	
	public function sendOTPPhone($phoneNumber);

	/**
	 * POST for verify phone api
	 * @param string $data
	 * @return string
	 */
	
	public function verifyPhone($data);

	/**
	 * POST check phone activated
	 * @param string $data
	 * @return bool
	 */
	
	public function isActivated($phoneNumber);

	/**
	 * POST for verify phone api by number
	 * @param string $data
	 * @return string
	 */
	
	public function verifyPhoneByNumber($data);

	/**
	 * Get time to resend OTP
	 * @param string $data
	 * @return string
	 */
	
	public function getTimeToResendOTP($phoneNumber);

	/**
	 * POST for Post api
	 * @param string $customerId
	 * @return string
	 */
	
	public function sendForgotPWOtp($phoneNumber);

	/**
	 * POST for Post api
	 * @param string $customerId
	 * @return string
	 */
	
	public function validateOtp($customerId, $phoneNumber, $otpCode);

	/**
	 * POST for Post api
	 * @param string $customerId
	 * @return string
	 */
	
	public function getValidatedCustomerId($phoneNumber);
}