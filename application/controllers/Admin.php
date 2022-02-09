<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('template', ['module' => strtolower($this->router->fetch_class())]);
		$this->load->model(['user', 'email_confirm']);
		if (empty($this->session->userdata($this->router->fetch_class())))
		{
			if (!in_array($this->router->fetch_method(), ['login', 'register', 'email_confirm', 'forgot_password', 'reset_password']))
			{
				redirect(base_url($this->router->fetch_class().'/login'), 'refresh');
			}
		}
	}

	public function index()
	{
		$this->template->load('home');
	}

	public function login()
	{
		if ($this->input->method() == 'post')
		{
			$this->form_validation->set_rules('identity', 'Email / Nama Pengguna', 'trim|required');
			$this->form_validation->set_rules('password', 'Kata Sandi', 'trim|required');
			if ($this->form_validation->run() == TRUE)
			{
				$user = $this->user->sign_in($this->input->post('identity'), $this->input->post('password'));
				if ($user->num_rows() >= 1)
				{
					$this->session->set_userdata(strtolower($this->router->fetch_class()), $user->row()->id);
					redirect(base_url($this->router->fetch_class()), 'refresh');
				}
				else
				{
					if ($this->user->search($this->input->post('identity'))->num_rows() >= 1)
					{
						$this->session->set_flashdata('login', array('status' => 'failed', 'message' => 'Kata sandi tidak sesuai'));
						redirect(base_url($this->router->fetch_class().'/'.$this->router->fetch_method()), 'refresh');
					}
					else
					{
						$this->session->set_flashdata('login', array('status' => 'failed', 'message' => 'Akun tidak ditemukan'));
						redirect(base_url($this->router->fetch_class().'/'.$this->router->fetch_method()), 'refresh');
					}
				}
			}
			else
			{
				$this->load->view('admin/login');
			}
		}
		else
		{
			$this->load->view('admin/login');
		}
	}

	public function profile($id = NULL, $option = NULL)
	{
		$data['profile'] = $this->user->read(array('id' => (!empty($id))?$id:$this->session->userdata(strtolower($this->router->fetch_class()))))->row();
		switch ($option)
		{
			case 'edit':
				if ($this->input->method() == 'post')
				{
					if ($id !== $this->session->userdata($this->router->fetch_class()) OR $id > $this->session->userdata($this->router->fetch_class()))
					{
						$this->session->set_flashdata('edit_profile', array('status' => 'failed', 'message' => 'Anda tidak memiliki akses untuk mengubah profil orang lain!'));
						redirect(base_url($this->router->fetch_class().'/profile/'.$id) ,'refresh');
					}

					$this->form_validation->set_data($this->input->post());
					$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_is_owned_data[user.email.'.strtolower($this->session->userdata($this->router->fetch_class()).']'));
					$this->form_validation->set_rules('username', 'Nama Pengguna', 'trim|required|callback_is_owned_data[user.username.'.strtolower($this->session->userdata($this->router->fetch_class()).']'));
					$this->form_validation->set_rules('full_name', 'Nama Lengkap', 'trim|required');

					if ($this->form_validation->run() == TRUE)
					{
						$update_data = array(
							'email' => $this->input->post('email'),
							'username' => $this->input->post('username'),
							'full_name' => $this->input->post('full_name')
						);

						if (!empty($this->input->post('password')))
						{
							$update_data['password'] = sha1($this->input->post('password'));
						}

						if (!empty($_FILES['photo']))
						{
							$config['upload_path'] = './uploads/';
							$config['allowed_types'] = 'png|jpg|jpeg';
							$config['file_name'] = url_title('user-profile-'.$id);
							$this->load->library('upload', $config);

							if (!$this->upload->do_upload('photo'))
							{
								$this->session->set_flashdata('upload_photo_error', $this->upload->display_errors());
							}
							else
							{
								// resize
								$config['image_library']	= 'gd2';
								$config['source_image']		= $this->upload->data()['full_path'];
								$config['maintain_ratio']	= TRUE;
								$config['width']			= 160;
								$config['height']			= 160;
								// watermark
								$config['wm_text'] 			= strtolower($this->router->fetch_class());
								$config['wm_type'] 			= 'text';
								$config['wm_font_color'] 	= 'ffffff';
								$config['wm_font_size'] 	= 12;
								$config['wm_vrt_alignment'] = 'middle';
								$config['wm_hor_alignment'] = 'center';
								$this->load->library('image_lib', $config);

								if ($this->image_lib->resize())
								{
									$this->image_lib->watermark();
								}

								$update_data['photo'] = $this->upload->data()['file_name'];
							}
						}

						$this->user->update($update_data, array('id' => $id));
						$this->session->set_flashdata('edit_profile', array('status' => 'success', 'message' => 'Profil berhasil diperbaharui!'));
						redirect(base_url($this->router->fetch_class().'/profile/'.$id) ,'refresh');
					}
					else
					{
						$this->template->load('profile_edit', $data);
					}
				}
				else
				{
					$this->template->load('profile_edit', $data);
				}
			break;

			default:
				$this->template->load('profile', $data);
			break;
		}
	}

	public function is_owned_data($val, $str)
	{
		$str = explode('.', $str);
		$data = $this->db->get('user', array($str[1] => $val));
		if ($data->num_rows() >= 1)
		{
			if ($data->row()->id == $str[2])
			{
				return TRUE;
			}
			else
			{
				$this->form_validation->set_message('is_owned_data', lang('form_validation_is_unique'));
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}

		return FALSE;
	}

	public function logout()
	{
		session_destroy();
		redirect(base_url($this->router->fetch_class().'/login'), 'refresh');
	}

	public function register()
	{
		if ($this->input->method() == 'post')
		{
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[user.email]|max_length[40]', array('is_unique' => 'Email sudah terdaftar!'));
			$this->form_validation->set_rules('full_name', 'Nama Lengkap', 'trim|required|max_length[40]');
			$this->form_validation->set_rules('password', 'Kata Sandi', 'trim|required');

			if ($this->form_validation->run() == TRUE)
			{
				$this->user->create(array(
					'email' => $this->input->post('email'),
					'password' => sha1($this->input->post('password')),
					'full_name' => $this->input->post('full_name')
				));

				$this->session->set_flashdata('register', array('status' => 'success', 'message' => 'Pendaftaran berhasil!!'));
				redirect(base_url($this->router->fetch_class().'/login'), 'refresh');
			}
			else
			{
				$this->load->view('admin/register');
			}
		}
		else
		{
			$this->load->view('admin/register');
		}
	}

	public function forgot_password()
	{
		if ($this->input->method() == 'post')
		{
			$search = $this->user->search($this->input->post('identity'));

			if ($search->num_rows() >= 1)
			{
				$code = random_string('numeric', 6);
				$this->load->library('email');
				$this->email->set_alt_message('Reset password');
				$this->email->to($search->row()->email);
				$this->email->from($this->config->item('smtp_user'), 'Skripsi');
				$this->email->subject('Ganti Kata Sandi');
				$data['link'] = base_url($this->router->fetch_class().'/reset_password/'.$code);
				$data['code'] = $code;
				$data['full_name'] = $search->row()->full_name;
				$this->email->message($this->load->view('email/reset_password', $data, TRUE));
				if (!$this->email->send())
				{
					$this->session->set_flashdata('forgot_password', array('status' => 'failed', 'message' => 'Sistem tidak bisa mengirim email!'));
					redirect(base_url($this->router->fetch_class().'/forgot_password'), 'refresh');
				}
				else
				{
					$this->email_confirm->new_code($search->row()->id, $code, 'reset-password');
					$this->session->set_flashdata('forgot_password', array('status' => 'success', 'message' => 'Email permintaan atur ulang kata sandi sudah dikirim, silahkan verifikasi <a href="'.base_url($this->router->fetch_class().'/email_confirm').'">disini</a>'));
					redirect(base_url($this->router->fetch_class().'/forgot_password'), 'refresh');
				}
			}
			else
			{
				$this->session->set_flashdata('forgot_password', array('status' => 'failed', 'message' => 'Sistem tidak menemukan akun!'));
				redirect(base_url($this->router->fetch_class().'/forgot_password'), 'refresh');
			}
		}
		else
		{
			$this->load->view('admin/forgot_password');
		}
	}

	/**
	 * Confirm email
	 *
	 * @param      integer  $code   Confirmation code
	 */
	public function email_confirm($code = NULL)
	{
		$data = array();

		if (!empty($code))
		{
			$data = array('confirm_code' => $code);
		}

		if ($this->input->method() == 'post')
		{
			$data = $this->input->post();
			$this->form_validation->set_rules('confirm_code', 'Confirm Code', 'trim|required');
			if ($this->form_validation->run() == TRUE)
			{
				$email_confirm = $this->email_confirm->review_confirm_code($data['confirm_code']);
				if ($email_confirm->num_rows() >= 1)
				{
					$email_confirm = $email_confirm->row();

					if ($email_confirm->status == 'unconfirmed')
					{
						if (now() < human_to_unix($email_confirm->expire_date))
						{
							if ($email_confirm->type == 'account-activation')
							{
								$this->email_confirm->confirm($data['confirm_code']);
								redirect(base_url($this->router->fetch_class().'/login'), 'refresh');
							}
							elseif ($email_confirm->type == 'reset-password')
							{
								$this->session->set_userdata('reset-password', $email_confirm->user_uid);
								$this->email_confirm->confirm($data['confirm_code']);
								redirect(base_url($this->router->fetch_class().'/reset_password'), 'refresh');
							}
							else
							{
								redirect(base_url(), 'refresh');
							}
						}
						else
						{
							$this->session->set_flashdata('email_confirm', array('status' => 'warning', 'message' => 'Masa waktu kode sudah habis'));
							redirect(base_url($this->router->fetch_class().'/email_confirm'), 'refresh');
						}
					}
					else
					{
						$this->session->set_flashdata('email_confirm', array('status' => 'warning', 'message' => 'Kode sudah pernah digunakan'));
						redirect(base_url($this->router->fetch_class().'/email_confirm'), 'refresh');
					}
				}
				else
				{
					$this->session->set_flashdata('email_confirm', array('status' => 'error', 'message' => 'Kode tidak ditemukan'));
					redirect(base_url($this->router->fetch_class().'/email_confirm'), 'refresh');
				}
			}
			else
			{
				$this->load->view('admin/email_confirm');
			}
		}
		else
		{
			$this->load->view('admin/email_confirm');
		}
	}

	public function reset_password($code = NULL)
	{
		if ($this->input->method() == 'post')
		{
			if ($this->session->has_userdata('reset-password'))
			{
				$this->form_validation->set_rules('new_password', 'Kata Sandi', 'trim|required');
				$this->form_validation->set_rules('repeat_new_password', 'Ulangi Kata Sandi', 'trim|required|matches[new_password]');

				if ($this->form_validation->run() == TRUE)
				{
					if ($this->user->update(array('password' => sha1($this->input->post('new_password'))), array('id' => $this->session->userdata('reset-password'))))
					{
						$this->session->unset_userdata('reset-password');
					}

					redirect(base_url($this->router->fetch_class().'/login'), 'refresh');
				}
				else
				{
					$this->load->view('admin/reset_password');
				}
			}
		}
		else
		{
			if ($this->session->has_userdata('reset-password'))
			{
				$this->load->view('admin/reset_password');
			}
			else
			{
				show_404();
			}
		}
	}
}

/* End of file Admin.php */
/* Location: ./application/controllers/Admin.php */
